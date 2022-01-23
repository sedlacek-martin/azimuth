<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Utils\ElFinderHelper;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class PageAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'page';

    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
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
        //Translations fields
        $titles = $descriptions = $metaTitles = $metaDescriptions = [];
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = [
                'label' => 'Title',
                'constraints' => [new NotBlank()],
            ];
            $descriptions[$locale] = [
                'label' => 'Content',
                'constraints' => [new NotBlank()],
                 'config' => [
                'filebrowserBrowseRoute' => 'elfinder',
                'filebrowserBrowseRouteParameters' => [
                    'instance' => 'ckeditor',
                    'homeFolder' => ElFinderHelper::getOrCreateFolder(ElFinderHelper::GLOBAL_DIR, $this->getKernelRoot()),
                ],
            ],
            ];
            $metaTitles[$locale] = [
                'label' => 'Meta Title',
                'constraints' => [new NotBlank()],
            ];
            $metaDescriptions[$locale] = [
                'label' => 'Meta Description',
                'constraints' => [new NotBlank()],
            ];
        }

        $formMapper
            ->with('Page')
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => [
                        'title' => [
                            'field_type' => 'text',
                            'locale_options' => $titles,
                            'required' => true,
                        ],
                        'description' => [
                            'field_type' => CKEditorType::class,
                            'locale_options' => $descriptions,
                            'required' => true,
                        ],
                        'metaTitle' => [
                            'field_type' => 'text',
                            'locale_options' => $metaTitles,
                            'required' => true,
                        ],
                        'metaDescription' => [
                            'field_type' => 'textarea',
                            'locale_options' => $metaDescriptions,
                            'required' => true,
                        ],
                        'slug' => [
                            'field_type' => 'text',
                            'disabled' => true,
                        ],
                    ],
//                    /** @Ignore */
                    'label' => 'Descriptions',
                ]
            )
            ->add(
                'published',
                null,
                [
                    'label' => 'admin.page.published.label',
                ]
            )
            ->add(
                'createdAt',
                null,
                [
                    'disabled' => true,
                    'label' => 'admin.page.created_at.label',
                ]
            )
            ->add(
                'updatedAt',
                null,
                [
                    'disabled' => true,
                    'label' => 'admin.page.updated_at.label',
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
                'translations.title',
                null,
                ['label' => 'admin.page.title.label']
            )
            ->add(
                'translations.description',
                null,
                ['label' => 'admin.page.description.label']
            )
            ->add(
                'published',
                null,
                ['label' => 'admin.page.published.label']
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                [
                    'label' => 'admin.page.created_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.createdAt,'%Y-%m-%d') = :createdAt")
                            ->setParameter('createdAt', $date->format('Y-m-d'));

                        return true;
                    },
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => ['format' => 'dd/MM/yyyy'],
                ],
                null
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
                'title',
                null,
                ['label' => 'admin.page.title.label']
            )
            ->add(
                'description',
                'html',
                [
                    'label' => 'admin.page.description.label',
                    'truncate' => [
                        'length' => 100,
                        'preserve' => true,
                    ],
                ]
            )
            ->add(
                'published',
                null,
                [
                    'editable' => true,
                    'label' => 'admin.page.published.label',
                ]
            )
            ->add(
                'createdAt',
                'date',
                [
                    'label' => 'admin.page.created_at.label',
                ]
            );

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
//                    'create' => array(),
                    'edit' => [],
                    'delete' => [],
                ],
            ]
        );
    }

    public function getExportFields()
    {
        return [
            'Id' => 'id',
            'Title' => 'title',
            'Description' => 'description',
            'Published' => 'published',
            'Created At' => 'createdAt',
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
//        $collection->remove('create');
//        $collection->remove('delete');
    }
}
