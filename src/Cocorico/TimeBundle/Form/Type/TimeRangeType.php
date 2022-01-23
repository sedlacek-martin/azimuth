<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Form\Type;

use Cocorico\TimeBundle\Form\DataTransformer\TimeRangeViewTransformer;
use Cocorico\TimeBundle\Model\DateRange;
use Cocorico\TimeBundle\Model\TimeRange;
use Cocorico\TimeBundle\Validator\TimeRangeValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeRangeType extends AbstractType
{
    protected $timezone;

    protected $timeUnit;

    protected $timeUnitIsDay;

    protected $timesMax;

    protected $hoursAvailable;

    /**
     * @param Session $session
     * @param int     $timeUnit in minute
     * @param int     $timesMax
     * @param array   $hoursAvailable
     */
    public function __construct(Session $session, $timeUnit, $timesMax, $hoursAvailable)
    {
        $this->timezone = $session->get('timezone');
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->timesMax = $timesMax;
        $this->hoursAvailable = $hoursAvailable;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->timeUnitIsDay) {
            throw new \Exception('Time ranges are only available for time unit not in day mode');
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();

                $timesMax = $this->timesMax;
                if (isset($options['times_max']) && $options['times_max']) {
                    $timesMax = $options['times_max'];
                }

                $timeUnit = $this->timeUnit;
                if (isset($options['time_unit']) && $options['time_unit']) {
                    $timeUnit = $options['time_unit'];
                }

                $form
                    ->add(
                        'start',
                        TimeType::class,
                        array_merge(
                            [
                                'label' => 'time_range.start',
                                'property_path' => 'start',
                                'placeholder' => '',
                                'widget' => 'choice',
                                'input' => 'datetime',
                                'model_timezone' => 'UTC',
                                'view_timezone' => $this->timezone,
                                'attr' => [
                                    'data-type' => 'start',
                                ],
                            ],
                            $options['start_options']
                        )
                    )->add(
                        'end',
                        TimeType::class,
                        array_merge(
                            [
                                'label' => 'time_range.end',
                                'property_path' => 'end',
                                'placeholder' => '',
                                'widget' => 'choice',
                                'input' => 'datetime',
                                'model_timezone' => 'UTC',
                                'view_timezone' => $this->timezone,
                                'attr' => [
                                    'data-type' => 'end',
                                ],
                            ],
                            $options['end_options']
                        )
                    )
                    ->add(
                        'date',//sync with DateRage start date for DST purpose
                        DateType::class,
                        [
                            'widget' => 'single_text',
                        ]
                    );

                //TimePicker
                $form
                    ->add(
                        'start_picker',
                        TimeType::class,
                        array_merge(
                            [
                                'mapped' => false,
                                'widget' => 'single_text',
                                /* @Ignore */
                                'label' => false,
                            ],
                            $options['start_picker_options']
                        )
                    )
                    ->add(
                        'end_picker',
                        TimeType::class,
                        array_merge(
                            [
                                'mapped' => false,
                                'widget' => 'single_text',
                                /* @Ignore */
                                'label' => false,
                            ],
                            $options['end_picker_options']
                        )
                    );

                //Times display mode: range or duration
                if ($options['display_mode'] == 'duration') {
                    if ($timesMax > 1) {
                        $nbMinutes = null;
                        if (isset($options['start_options']['data'], $options['end_options']['data'])) {
                            $timeRange = new TimeRange(
                                $options['start_options']['data'],
                                $options['end_options']['data']
                            );
                            $nbMinutes = $timeRange->getDuration($timeUnit) * $timeUnit;
                        }

                        /* @var DateRange $dateRange */
                        $form
                            ->add(
                                'nb_minutes',
                                ChoiceType::class,
                                [
                                    //from one time unit to timesMax * timeUnit
                                    'choices' => array_combine(
                                        range(1, $timesMax),
                                        range($timeUnit, $timesMax * $timeUnit, $timeUnit)
                                    ),
                                    'data' => $nbMinutes,
                                    /* @Ignore */
                                    'placeholder' => '',
                                    'attr' => [
                                        'class' => 'no-scroll no-arrow',
                                    ],
                                ]
                            );
                    } else {//One time unit. $this->timesMax = 1
                        $form
                            ->add(
                                'nb_minutes',
                                HiddenType::class,
                                [
                                    'data' => $timeUnit,
                                ]
                            );
                    }
                }
            }
        );

        //Set TimeRange date field from DateRange start field.
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($options) {
                $this->setTimeRangeDate($event);
            }
        );

        $builder->addViewTransformer($options['transformer']);
        $builder->addEventSubscriber($options['validator']);
    }

    /**
     * Set TimeRange date field from DateRange start field. Used in TimeRangeViewTransformer to assigned date to time range
     *
     * @param FormEvent $event
     */
    private function setTimeRangeDate(FormEvent $event)
    {
        //Search date range field in form containing time range field
        $form = $event->getForm();
        $parentForm = $form->getParent();
        $grandParentForm = $parentForm->getParent();

        /** @var DateRange $dateRange */
        $dateRange = null;
        if ($form && $form->has('date_range')) {
            $dateRange = $form->get('date_range')->getData();
        } elseif ($parentForm && $parentForm->has('date_range')) {
            $dateRange = $parentForm->get('date_range')->getData();
        } elseif ($grandParentForm && $grandParentForm->has('date_range')) {
            $dateRange = $grandParentForm->get('date_range')->getData();
        }

        //Set date of time range equal to date range start field
        if ($dateRange && $dateRange->getStart()) {
            $timeRange = $event->getData();

            $timeRange['date'] = $dateRange->getStart()->format('Y-m-d');
            $event->setData($timeRange);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Cocorico\TimeBundle\Model\TimeRange',
                'start_options' => [],
                'end_options' => [],
                'start_picker_options' => [],
                'end_picker_options' => [],
                'transformer' => null,
                'validator' => null,
                'translation_domain' => 'cocorico_listing',
                'display_mode' => 'range',
                'times_max' => null,
                'time_unit' => null,
            ]
        );

        $resolver->setAllowedTypes(
            'transformer',
            ['Symfony\Component\Form\DataTransformerInterface', 'null']
        );

        $resolver->setAllowedTypes(
            'validator',
            ['Symfony\Component\EventDispatcher\EventSubscriberInterface', 'null']
        );

        // Those normalizers lazily create the required objects and handle timezone DST
        $resolver->setNormalizer(
            'transformer',
            function (Options $options, $value) {
                $value = new TimeRangeViewTransformer(
                    new OptionsResolver(), [
                        'timezone' => $this->timezone,
                    ]
                );

                return $value;
            }
        );

        $resolver->setNormalizer(
            'validator',
            function (Options $options, $value) {
                if (!$value) {
                    $value = new TimeRangeValidator(
                        new OptionsResolver(), [
                            'required' => $options['required'],
                            'hours_available' => $this->hoursAvailable,
                        ]
                    );
                }

                return $value;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'time_range';
    }
}
