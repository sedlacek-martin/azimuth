<?php


namespace Cocorico\UserBundle\Admin;


use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\CoreBundle\Form\Type\EqualType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ActivationAdmin extends BaseUserAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'verification';
    protected $baseRouteName = 'verification';
    protected $locales;

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
    }


    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('email', null, [])
            ->add('fullName', null, [])
            ->add('birthday', null, [])
            ->add('verifiedDomainRegistration', null, [
                'label' => 'Registration type',
                'template' => 'CocoricoSonataAdminBundle::list_field_registration_type.html.twig',
            ])
            ->add('enabled', null, [])
            ->add('trusted', null, [])
            ->add('reconfirmRequested', null, []);

        if ($this->getUser() && $this->getUser()->getMemberOrganization()->isRequiresUserIdentifier()) {
            $listMapper
                ->add('organizationIdentifier', null, [
                    'template' => 'CocoricoSonataAdminBundle::list_field_mo_unique_identifier.html.twig',
                ]);
        }

        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('memberOrganization', null, []);
        }


        $actions = [
            'actions' => [
                'list_user_trusted' => [
                    'template' => 'CocoricoSonataAdminBundle::list_action_trusted.html.twig',
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('trusted', null, [
                'advanced_filter' => false,
            ])
            ->add('email', null, [
                'advanced_filter' => false
            ]);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
    }


    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere($query->getRootAliases()[0] . '.trusted = 0 OR '. $query->getRootAliases()[0] . '.reconfirmRequested = 1');
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.memberOrganization', 'mo')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }
}