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
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\ListingImageType;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Cocorico\UserBundle\Repository\UserRepository;
use Doctrine\ORM\Query\Expr;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ListingAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'listing';

    protected $locales;

    protected $includeVat;

    protected $bundles;

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
     * @param bool $includeVat
     */
    public function setIncludeVat($includeVat)
    {
        $this->includeVat = $includeVat;
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Listing $listing */
        $listing = $this->getSubject();

        $offererQuery = null;
        if ($listing) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $offererQuery = $userRepository->getFindOneQueryBuilder($listing->getUser()->getId());
        }

        //Translations fields
        $titles = $descriptions = $rules = [];
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = [
                'label' => 'Title',
            ];
            $descriptions[$locale] = [
                'label' => 'Description',
                'config' => [
                    'toolbar' => 'basic',
                ],
            ];
            $rules[$locale] = [
                'label' => 'Rules',
            ];
        }

        $formMapper
            ->with('admin.listing.title')
            ->add(
                'status',
                ChoiceType::class,
                [
                    'choices' => array_flip(Listing::$statusValues),
                    'placeholder' => 'admin.listing.status.label',
                    'translation_domain' => 'cocorico_listing',
                    'label' => 'admin.listing.status.label',
                ]
            )
            ->add(
                'adminNotation',
                ChoiceType::class,
                [
                    'choices' => array_combine(
                        range(0, 10, 0.5),
                        array_map(
                            function ($num) {
                                return number_format($num, 1);
                            },
                            range(0, 10, 0.5)
                        )
                    ),
                    'placeholder' => 'admin.listing.admin_notation.label',
                    'label' => 'admin.listing.admin_notation.label',
                    'required' => false,
                ]
            )
            ->add(
                'certified',
                null,
                [
                    'label' => 'admin.listing.certified.label',
                ]
            )
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
                        ],
                        'description' => [
                            'field_type' => CKEditorType::class,
                            'locale_options' => $descriptions,
                        ],
                        'rules' => [
                            'field_type' => 'textarea',
                            'locale_options' => $rules,
                            'required' => false,
                        ],
                        'slug' => [
                            'display' => false,
                        ],
                    ],
                    'label' => 'Descriptions',
                ]
            )
            ->add(
                'user',
                'sonata_type_model',
                [
                    'query' => $offererQuery,
                    'disabled' => true,
                    'label' => 'admin.listing.user.label',
                ]
            )
            ->add(
                'category',
                null,
                [
                    'disabled' => true,
                    'label' => 'admin.listing.categories.label',
                ]
            )
            ->add(
                'images',
                CollectionType::class,
                [
                    'entry_type' => ListingImageType::class,
                    'by_reference' => false,
                    'required' => false,
                    'disabled' => true,
                    'prototype' => true,
                    'allow_add' => false,
                    'allow_delete' => false,

                    'label' => 'admin.listing.images.label',
                ]
            );

        $formMapper
            ->add(
                'location.completeAddress',
                'text',
                [
                    'disabled' => true,
                    'label' => 'admin.listing.location.label',
                ]
            )
            ->add(
                'createdAt',
                null,
                [
                    'disabled' => true,
                    'label' => 'admin.listing.created_at.label',
                ]
            )
            ->add(
                'updatedAt',
                null,
                [
                    'disabled' => true,
                    'label' => 'admin.listing.updated_at.label',
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
                'fullName',
                'doctrine_orm_callback',
                [
                    'callback' => [$this, 'getFullNameFilter'],
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => [],
                    'label' => 'admin.listing.offerer.label',
                ]
            )
            ->add(
                'user.email',
                null,
                ['label' => 'admin.listing.user_email.label']
            )
            ->add(
                'category',
                null,
                ['label' => 'admin.listing.categories.label']
            )
            ->add(
                'status',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => array_flip(Listing::$statusValues),
                    'translation_domain' => 'cocorico_listing',
                    'label' => 'admin.listing.status.label',
                ]
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                [
                    'label' => 'admin.listing.created_at.label',
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
            )
            ->add(
                'updatedAt',
                'doctrine_orm_callback',
                [
                    'label' => 'admin.listing.updated_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.updatedAt,'%Y-%m-%d') = :updatedAt")
                            ->setParameter('updatedAt', $date->format('Y-m-d'));

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
                'statusText',
                null,
                [
                    'label' => 'admin.listing.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_listing',
                ]
            )
            ->add(
                'user',
                null,
                ['label' => 'admin.listing.user.label']
            )
            ->add(
                'user.email',
                null,
                ['label' => 'admin.listing.user_email.label']
            )
            ->add(
                'title',
                null,
                ['label' => 'admin.listing.title.label']
            )
            ->add('category', null, [
                'label' => 'admin.listing.category.label',
            ]);

        $listMapper
            ->add(
                'updatedAt',
                'date',
                [
                    'label' => 'admin.listing.updated_at.label',
                ]
            );

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                    'show' => [
                        'template' => 'CocoricoSonataAdminBundle::list_action_listing_show.html.twig',
                    ],
                ],
            ]
        );
    }

    public function getFullNameFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $exp = new Expr();
        $queryBuilder
            ->andWhere(
                $exp->orX(
                    $exp->like('s_user.firstName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like('s_user.lastName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like(
                        $exp->concat(
                            's_user.firstName',
                            $exp->concat($exp->literal(' '), 's_user.lastName')
                        ),
                        $exp->literal('%' . $value['value'] . '%')
                    )
                )
            );

        return true;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    public function getExportFields()
    {
        return [
            'Id' => 'id',
            'Status' => 'statusText',
            'Offerer' => 'user.fullname',
            'Email' => 'user.Email',
            'Phone' => 'user.Phone',
            'Listing Title' => 'title',
            'Price' => 'priceDecimal',
            'Updated At' => 'updatedAt',
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

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.user', 'user')
                ->join('user.memberOrganization', 'mo')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}
