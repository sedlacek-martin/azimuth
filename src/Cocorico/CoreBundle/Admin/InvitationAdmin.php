<?php

namespace Cocorico\CoreBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class InvitationAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'invitations';
    protected $baseRouteName = 'invitations';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [])
            ->add('email', null, array())
            ->add('used', null, array())
            ->add('expiration', null, array())
            ->add('createdAt', null, array());
//            ->add('certified', null, ['editable' => true]);

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'delete' => array(),
                    'edit' => array(),
                )
            )
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('email', EmailType::class)
            ->add('expiration', DateTimeType::class)
            ->add('used', CheckboxType::class, [
                'required' => false,
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('used', null,  []);
    }

//    protected function configureDefaultFilterValues(array &$filterValues)
//    {
//        $filterValues['certified'] = [
//            'type' => EqualType::TYPE_IS_EQUAL,
//            'value' => BooleanType::TYPE_NO,
//        ];
//    }

    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('create');
//        $collection->remove('delete');
    }

//    public function createQuery($context = 'list')
//    {
//        $token= $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken();
//        $currentUser = $token ? $token->getUser() : null;
//        /** @var QueryBuilder $query */
//        $query = parent::createQuery($context);
//        //COUNTRY_ADMIN can only see users from his country
//        if (isset($currentUser) && $currentUser->hasRole("ROLE_COUNTRY_ADMIN")) {
//
//            $query->join($query->getRootAliases()[0] . '.user', 'u')
//                ->andWhere(
//                    $query->expr()->eq('u.countryOfResidence', ':country')
//                )->setParameter(':country', $currentUser->getCountryOfResidence());
//        }
//
//        return $query;
//    }

}