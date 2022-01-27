<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CMSBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CMSBundle\Entity\Footer;
use Cocorico\CMSBundle\Model\Manager\FooterManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class FooterAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'footer';

    protected $locales;

    /** @var  FooterManager */
    protected $footerManager;

    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setFooterManager(FooterManager $footerManager)
    {
        $this->footerManager = $footerManager;
    }

    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        //Translations fields
        $titles = $links = $urls = $urlsHash = [];
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = [
                'label' => 'Title',
                'constraints' => [new NotBlank()],
            ];
            $links[$locale] = [
                'label' => 'Link',
                'constraints' => [new NotBlank()],
            ];
            $urls[$locale] = [
                'label' => 'URL',
            ];
            $urlsHash[$locale] = [
                'label' => 'URL Hash',
            ];
        }

        $formMapper
            ->with('admin.footer.title')
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => [
                        'url' => [
                            'field_type' => 'url',
                            'locale_options' => $urls,
                            'required' => false,
                        ],
                        'urlHash' => [
                            'field_type' => 'text',
                            'locale_options' => $urlsHash,
                            'required' => false,
                            'disabled' => true,
                        ],
                        'title' => [
                            'field_type' => 'text',
                            'locale_options' => $titles,
                            'required' => true,
                        ],
                        'link' => [
                            'field_type' => 'url',
                            'locale_options' => $links,
                            'required' => true,
                        ],
                    ],
                    /* @Ignore */
                    'label' => 'Descriptions',
                    'help' => 'admin.footer.help',
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
                'translations.link',
                null,
                ['label' => 'admin.page.link.label']
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
                'link',
                'url',
                [
                    'label' => 'admin.page.link.label',
                    'truncate' => [
                        'length' => 100,
                        'preserve' => true,
                    ],
                ]
            )
            ->add(
                'url',
                'html',
                [
                    'label' => 'admin.footer.url.label',
                    'truncate' => [
                        'length' => 50,
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
                null,
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
            'Links' => 'link',
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

    /**
     * @param mixed|Footer $footer
     * @return mixed
     */
    public function postPersist($footer)
    {
        return $this->footerManager->save($footer);
    }

    /**
     * @param mixed|Footer $footer
     * @return mixed
     */
    public function postUpdate($footer)
    {
        return $this->footerManager->save($footer);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('create');
//        $collection->remove('delete');
    }
}
