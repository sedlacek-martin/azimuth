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
use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListingCharacteristicGroupAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'listing-characteristic-group';

    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'id',
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
        }

        $formMapper
            ->with('admin.listing_characteristic_group.title')
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
                    ],
                    /* @Ignore */
                    //'label' => 'Descriptions'
                ]
            )
            ->add('position', null, ['label' => 'admin.listing_characteristic_group.position.label'])
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
                ['label' => 'admin.listing_characteristic_group.name.label']
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
                    'label' => 'admin.listing_characteristic_group.name.label',
                ]
            );

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

    protected function configureRoutes(RouteCollection $collection)
    {
        //$collection->remove('create');
        //$collection->remove('delete');
    }
}
