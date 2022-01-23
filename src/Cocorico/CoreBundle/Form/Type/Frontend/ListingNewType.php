<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Event\ListingFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingFormEvents;
use Cocorico\CoreBundle\Form\Type\ImageType;
use Cocorico\TimeBundle\Form\Type\DateRangeType;
use Cocorico\UserBundle\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class ListingNewType
 * Categories are created trough ajax in ListingNewCategoriesType.
 */
class ListingNewType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'listing.form.tac.error';

    public static $credentialError = 'user.form.credential.error';

    private $request;

    protected $dispatcher;

    private $locale;

    private $locales;

    /**
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $locales
     */
    public function __construct(
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        $locales
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Listing $listing */
        $listing = $builder->getData();

        //Translations fields
        $titles = $descriptions = [];
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = [
                'label' => 'listing.form.title',
                'constraints' => [new NotBlank(),
                                       new Length(
                                           [
                                            'max' => 50,
                                            'min' => 3,
                                                ]
                                            ),
                                       ],
                'attr' => [
                    'placeholder' => 'auto',
                ],
            ];
            $descriptions[$locale] = [
                'label' => 'listing.form.description',
                'constraints' => [new NotBlank()],
//                'attr' => array(
//                    'placeholder' => 'auto',
//                ),
                'config' => [
                    'toolbar' => 'basic',
                ],
            ];
        }

        $builder
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'required_locales' => [$this->locale],
                    'fields' => [
                        'title' => [
                            'field_type' => 'text',
                            'locale_options' => $titles,
                        ],
                        'description' => [
                            'field_type' => CKEditorType::class,
                            'locale_options' => $descriptions,
                        ],
                        'rules' => [
                            'display' => false,
                        ],
                        'slug' => [
                            'display' => false,
                        ],
                    ],
                    /* @Ignore */
                    'label' => false,
                ]
            )

            ->add(
                'dateRange',
                DateRangeType::class,
                [
                    'start_options' => [
                        'label' => 'listing.form.valid_from.label',
                    ],
                    'end_options' => [
                        'label' => 'listing.form.valid_to.label',
                    ],
                    'allow_single_day' => true,
                    'end_day_included' => true,
                    'block_name' => 'date_range',
                    'required' => false,
                    /* @Ignore */
                    'label' => 'listing.form.valid',
                ]
            )
            ->add(
                'image',
                ImageType::class
            )
            ->add(
                'location',
                ListingLocationType::class,
                [
                    'data_class' => 'Cocorico\CoreBundle\Entity\ListingLocation',
                    /* @Ignore */
                    'label' => false,
                    'data' => $this->getDefaultListingLocation($listing->getUser()),
                ]
            )
            ->add(
                'tac',
                CheckboxType::class,
                [
                    'label' => 'listing.form.tac',
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        [
                            'message' => self::$tacError,
                        ]
                    ),
                ]
            );

        //Dispatch LISTING_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example to add fields to new listing form
        $this->dispatcher->dispatch(
            ListingFormEvents::LISTING_NEW_FORM_BUILD,
            new ListingFormBuilderEvent($builder)
        );
    }

    /**
     * @param User $user
     * @return ListingLocation|null
     */
    private function getDefaultListingLocation(User $user)
    {
        $listingLocation = new ListingLocation();
        if ($user->getListings()->count() >= 1) {
            /** @var Listing $listing */
            $listing = $user->getListings()->first();
            $location = $listing->getLocation();

            $listingLocation->setCountry($location->getCountry());
            $listingLocation->setCity($location->getCity());
            $listingLocation->setZip($location->getZip());
            $listingLocation->setRoute($location->getRoute());
            $listingLocation->setStreetNumber($location->getStreetNumber());
        } else {
            $listingLocation->setCountry($user->getCountry());
        }

        return $listingLocation;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_new',
                'translation_domain' => 'cocorico_listing',
                'constraints' => new Valid(),
                //'validation_groups' => array('Listing'),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_new';
    }

    /**
     * JMS Translation messages.
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = [];
        $messages[] = new Message(self::$tacError, 'cocorico');
        $messages[] = new Message(self::$credentialError, 'cocorico');

        return $messages;
    }
}
