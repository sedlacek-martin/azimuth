<?php

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\TimeBundle\Form\Type\DateRangeType;
use DateInterval;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ListingEditDatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var Listing $listing */
        $listing = $builder->getData();
        $dateRange = $listing->getDateRange();

        $builder
            ->add(
                'dateRange',
                DateRangeType::class,
                [
                    'start_options' => [
                        'label' => 'listing.form.valid_from.label',
                        'data' => $dateRange && $dateRange->getStart() ? $dateRange->getStart() : null,
                    ],
                    'end_options' => [
                        'label' => 'listing.form.valid_to.label',
                        'data' => $dateRange && $dateRange->getEnd() ? $dateRange->getEnd() : null,
                    ],
                    'allow_single_day' => true,
                    'end_day_included' => true,
                    'block_name' => 'date_range',
                    'required' => false,
                    /* @Ignore */
                    'label' => 'listing.form.valid',
                ]
            )
            ->add('expiryDate',
            DateType::class, [
                    'label' => 'listing.form.expiry_date.label',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'class' => 'form-control',
                        'data-max-date' => (new DateTime())->add(new DateInterval('P1Y'))->format('Y-m-d'),
                    ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'data_class' => Listing::class,
                'csrf_token_id' => 'listing_edit',
                'translation_domain' => 'cocorico_listing',
                'constraints' => new Valid(),//To have error on collection item field,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'listing_edit_dates';
    }
}
