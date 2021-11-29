<?php

namespace Cocorico\CoreBundle\Admin;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CountryInformationAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'country-information';
    protected $baseRouteName = 'country-information';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'íd'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [])
            ->add('country', 'country', [])
            ->add('description', 'html', [
                'label' => 'admin.page.description.label',
                'truncate' => [
                    'length' => 75,
                    'preserve' => true
                ]
            ]);

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => [],
                )
            )
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('country', 'country')
            ->add('description', CKEditorType::class, [
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('country', null,  []);
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
        $collection->remove('create');
        $collection->remove('delete');
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