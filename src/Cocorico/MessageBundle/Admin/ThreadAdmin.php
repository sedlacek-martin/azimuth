<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Admin;

use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ThreadAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'thread';

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
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'createdBy',
                null,
                [
                    'label' => 'admin.thread.from.label',
                    'associated_tostring' => 'getName',
                ]
            )
            ->add(
                'createdAt',
                null,
                [
                    'label' => 'admin.thread.createdAt.label',
                ]
            );

        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('createdBy.memberOrganization', null, [
                    'label' => 'admin.thread.member_organization.label',
                ]);
        }

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'validate' => [
                        'template' => 'CocoricoSonataAdminBundle::list_action_message_validate.html.twig',
                    ],
                ],
            ]
        );
    }

//    /** @inheritdoc */
//    protected function configureFormFields(FormMapper $formMapper)
//    {
//        /** @var Thread $thread */
//        $thread = $this->getSubject();
//
//        $listing = null;
//        if ($thread) {
//            if ($thread->getListing()) {
//                $listing = $thread->getListing();
//            } elseif ($thread->getBooking()) {
//                $listing = $thread->getBooking()->getListing();
//            }
//
//            if ($listing) {
//                /** @var ListingRepository $listingRepository */
//                $listingRepository = $this->modelManager->getEntityManager('CocoricoCoreBundle:Listing')
//                    ->getRepository('CocoricoCoreBundle:Listing');
//
//                $listingQuery = $listingRepository->getFindOneByIdAndLocaleQuery(
//                    $listing->getId(),
//                    $this->request ? $this->getRequest()->getLocale() : 'fr'
//                );
//
//                $formMapper
//                    ->add(
//                        'listing',
//                        'sonata_type_model',
//                        array(
//                            'query' => $listingQuery,
//                            'disabled' => true,
//                            'label' => 'admin.review.listing.label',
//                        )
//                    );
//            }
//        }
//
//
//        $formMapper
//            ->add(
//                'messages',
//                'sonata_type_collection',
//                array(
//                    // IMPORTANT!: Disable this field otherwise if child form has all its fields disabled
//                    // then the child entities will be removed while saving
//                    'disabled' => true,
//                    'type_options' => array(
//                        // Prevents the "Delete" option from being displayed
//                        'delete' => false,
//                    )
//                ),
//                array(
//                    'edit' => 'inline',
//                    'delete' => 'false',
//                    'inline' => 'table',
//                    'sortable' => 'position'
//                )
//            )
//            ->end();
//    }

//    public function getFormTheme()
//    {
//        return array_merge(
//            parent::getFormTheme(),
//            array('CocoricoMessageBundle:Admin:message_body.html.twig')
//        );
//    }

    /**
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'fromName',
                'doctrine_orm_callback',
                [
                    'callback' => [$this, 'getFromNameFilter'],
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => [],
                    'label' => 'admin.thread.from.label',
                ]
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                [
                    'label' => 'admin.thread.created_at.label',
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

    public function getFromNameFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $exp = new Expr();
        $queryBuilder
            ->join('o.createdBy', 'bu')
            ->andWhere(
                $exp->orX(
                    $exp->like('bu.firstName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like('bu.lastName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like(
                        $exp->concat(
                            'bu.firstName',
                            $exp->concat($exp->literal(' '), 'bu.lastName')
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

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y'); //change this to suit your needs

        return $dataSourceIt;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
        $collection->remove('edit');
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.messages', 'm')
                ->join($query->getRootAliases()[0] . '.metadata', 'tm')
                ->join('tm.participant', 'p')
                ->join('p.memberOrganization', 'mo')
                ->andWhere('m.verified = 0')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }
}
