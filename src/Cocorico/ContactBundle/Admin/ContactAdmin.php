<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Admin;

use Cocorico\ContactBundle\Entity\Contact;
use Cocorico\ContactBundle\Model\BaseContact;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContactAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'contact';

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'status'
    );

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.contact.title')
            ->add(
                'firstName',
                null,
                array(
                    'label' => 'admin.contact.first_name.label'
                )
            )
            ->add(
                'lastName',
                null,
                array(
                    'label' => 'admin.contact.last_name.label',
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => 'admin.contact.email.label',
                )
            )
            ->add(
                'subject',
                null,
                array(
                    'label' => 'admin.contact.subject.label',
                )
            )
            ->add(
                'message',
                null,
                array(
                    'label' => 'admin.contact.message.label',
                )
            )
            ->add(
                'status',
                ChoiceType::class,
                array(
                    'choices' => array_flip(Contact::$statusValues),
                    'label' => 'admin.contact.status.label',
                    'translation_domain' => 'cocorico_contact',
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.contact.created_at.label',
                )
            )
            ->end();
    }


    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'status',
                'doctrine_orm_string',
                array(),
                ChoiceType::class,
                array(
                    'choices' => array_flip(Contact::$statusValues),
                    'label' => 'admin.contact.status.label',
                    'translation_domain' => 'cocorico_contact',
                )
            )
            ->add(
                'firstName',
                null,
                array('label' => 'admin.contact.first_name.label')
            )
            ->add(
                'lastName',
                null,
                array('label' => 'admin.contact.last_name.label')
            )
            ->add(
                'email',
                null,
                array('label' => 'admin.contact.email.label')
            )
            ->add(
                'subject',
                null,
                array('label' => 'admin.contact.subject.label')
            )
            ->add(
                'recipientRoles',
                'doctrine_orm_string',
                array(),
                ChoiceType::class,
                array(
                    'choices' => array_flip(BaseContact::RECIPIENT_ROLES),
                    'label' => 'admin.contact.status.label',
                    'translation_domain' => 'cocorico_contact',
                )
            )
            ->add(
                'createdAt',
                null,
                array('label' => 'admin.contact.created_at.label')
            );
    }


    /** @inheritdoc */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.contact.status.label',
                    'data_trans' => 'cocorico_contact',
                     'template' => 'CocoricoSonataAdminBundle::list_field_contact_status.html.twig',
                )
            )
            ->add(
                'firstName',
                null,
                array('label' => 'admin.contact.first_name.label')
            )
            ->add(
                'lastName',
                null,
                array('label' => 'admin.contact.last_name.label')
            )
            ->add(
                'email',
                null,
                array('label' => 'admin.contact.email.label')
            )
            ->add(
                'subject',
                null,
                array('label' => 'admin.contact.subject.label')
            )
            ->add(
                'createdAt',
                null,
                array(
                    'label' => 'admin.contact.created_at.label',
                )
            );
        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('recipientRoleNames', null, [
                    'label' => 'admin.contact.recipient_roles.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_array.html.twig',
                    'data_trans' => 'SonataAdminBundle'
                ]);
        }

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'show' => array(),
                )
            )
        );
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('User information')
            ->add('user')
            ->add('firstName')
            ->add('lastName')
            ->add('email', null, [
                'template' => 'CocoricoSonataAdminBundle::show_field_email.html.twig',
            ])
            ->add('createdAt')
            ->add('replySend')
            ->end()
            ->with('Message')
            ->add('subject')
            ->add('message')
            ->end()
            ->with('Reply')
            ->add('id', null, [
                'template' => 'CocoricoSonataAdminBundle::contact_show_actions.html.twig',
            ])
            ->end();
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);
        return $actions;
    }


    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $datasourceit = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $datasourceit->setDateTimeFormat('d M Y'); //change this to suit your needs
        return $datasourceit;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
        $collection->remove('edit');

    }

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);

        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $rootAlias = $query->getRootAliases()[0];
            $query
                ->leftJoin($query->getRootAliases()[0] . '.user', 'user')
                ->leftJoin('user.memberOrganization', 'mo')
                ->andWhere($query->expr()->orX(
                    $query->expr()->andX("{$rootAlias}.user IS NOT NULL", 'mo.id = :moId'),
                    $query->expr()->isNull("{$rootAlias}.user")
                ))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
            if ($this->authIsGranted('ROLE_ACTIVATOR') && $this->getUser() !== null) {
                $query->andWhere($query->expr()->like($rootAlias .'.recipientRoles', $query->expr()->literal('%ROLE_ACTIVATOR%')));
            } elseif ($this->authIsGranted('ROLE_FACILITATOR') && $this->getUser() !== null) {
                $query->andWhere($query->expr()->like($rootAlias .'.recipientRoles', $query->expr()->literal('%ROLE_FACILITATOR%')));
            }
        }

        return $query;
    }
}
