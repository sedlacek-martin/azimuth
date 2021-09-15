<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\BaseListing;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ListingValidationAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'listing-validation';
    protected $baseRouteName = 'listing-validation';
    protected $locales;

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.listing.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_listing'
                )
            )
            ->add(
                'user',
                null,
                array('label' => 'admin.listing.user.label')
            )
            ->add(
                'user.email',
                null,
                array('label' => 'admin.listing.user_email.label')
            )
            ->add(
                'title',
                null,
                array('label' => 'admin.listing.title.label')
            )
            ->add(
                'updatedAt',
                'datetime',
                array(
                    'label' => 'admin.listing.updated_at.label',
                )
            );

        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('user.memberOrganization', null, []);
        }

        $actions = [
            'actions' => [
                'list_user_show_listing' => [
                    'template' => 'CocoricoSonataAdminBundle::list_action_listing_show.html.twig',
                ],
                'delete' => [],
            ],
        ];

        $listMapper
            ->add(
                '_action',
                'actions',
                $actions
            );
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'fullName',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getFullNameFilter'),
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => array(),
                    'label' => 'admin.listing.offerer.label'
                )
            )
            ->add(
                'user.email',
                null,
                array('label' => 'admin.listing.user_email.label')
            )
            ->add(
                'updatedAt',
                'doctrine_orm_callback',
                array(
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
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                )
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
        $collection->remove('edit');
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere($query->getRootAliases()[0] . '.status = ' . BaseListing::STATUS_TO_VALIDATE);
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.user', 'user')
                ->join('user.memberOrganization', 'mo')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }



}