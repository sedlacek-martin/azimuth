<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingListingCharacteristic;
use Cocorico\CoreBundle\Repository\ListingListingCharacteristicRepository;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListingCharacteristicAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'listing-characteristic';

    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ListingCharacteristic $subject */
//        $subject = $this->getSubject();

        //Translations fields
        $titles = $descriptions = [];
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = [
                'label' => 'Name',
                'constraints' => [new NotBlank()],
            ];
            $descriptions[$locale] = [
                'label' => 'Description',
                'constraints' => [new NotBlank()],
            ];
        }

        $formMapper
            ->with('admin.listing_characteristic.title')
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => [
                        'name' => [
                            'field_type' => 'text',
                            'locale_options' => $titles,
                        ],
                        'description' => [
                            'field_type' => 'textarea',
                            'locale_options' => $descriptions,
                        ],
                    ],
                    /* @Ignore */
                    'label' => 'Descriptions',
                ]
            )
            ->add(
                'position',
                null,
                [
                    'label' => 'admin.listing_characteristic.position.label',
                ]
            )
            ->add('filter', null, [
                'label' => 'admin.listing_characteristic.filter.label',
                'help' => 'admin.listing_characteristic.filter.help',
            ])
            ->add(
                'listingCharacteristicType',
                'sonata_type_model_list',
                [
                    'label' => 'admin.listing_characteristic.type.label',
                    'constraints' => [new NotBlank()],
                ]
            )
            ->add(
                'listingCharacteristicGroup',
                'sonata_type_model_list',
                [
                    'label' => 'admin.listing_characteristic.group.label',
                    'constraints' => [new NotBlank()],
                ]
            )
            ->add(
                'listingCategories',
                EntityType::class,
                [
                    'label' => 'admin.listing_characteristic.categories.label',
                    'class' => ListingCategory::class,
                    'multiple' => true,
                    'help' => 'admin.listing_characteristic.categories.help',
                ]
            )
            ->end();
    }

    /**
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'translations.name',
                null,
                ['label' => 'admin.listing_characteristic.name.label']
            )
            ->add(
                'listingCharacteristicType',
                null,
                ['label' => 'admin.listing_characteristic.type.label']
            )
            ->add(
                'listingCharacteristicGroup',
                null,
                ['label' => 'admin.listing_characteristic.group.label']
            );
    }

    /**
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'name',
                null,
                [
                    'label' => 'admin.listing_characteristic.name.label',
                ]
            )
            ->addIdentifier(
                'listingCharacteristicType',
                null,
                ['label' => 'admin.listing_characteristic.type.label']
            )
            ->addIdentifier(
                'listingCharacteristicGroup',
                null,
                ['label' => 'admin.listing_characteristic.group.label']
            )
            ->add(
                'position',
                null,
                ['label' => 'admin.listing_characteristic.position.label']
            )
            ->add('filter', null, [
                'label' => 'admin.listing_characteristic.filter.label',
            ]);

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    //'show' => array(),
                    'edit' => [],
                ],
            ]
        );
    }

    public function getExportFields()
    {
        return [
            'Id' => 'id',
            'Name' => 'name',
            'Type of Characteristic' => 'listingCharacteristicType',
            'Group' => 'listingCharacteristicGroup',
            'Position' => 'position',
        ];
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y'); //change this to suit your needs

        return $dataSourceIt;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @param ListingCharacteristic $object
     */
    public function postUpdate($object)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var ListingListingCharacteristicRepository $llcRepository */
        $llcRepository = $em->getRepository(ListingListingCharacteristic::class);

        $llcRepository->deleteByCharacteristicAndCategory(
            $object->getId(),
            ...$object->getListingCategories()->map(function (ListingCategory $cat) {
                return $cat->getId();
            })->toArray()
        );

//        $llcRepository->deleteByCharacteristicAndCategory(
//            $object,
//            $object->getListingCategories()->toArray()
//        );
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        //$collection->remove('create');
        //$collection->remove('delete');
    }
}
