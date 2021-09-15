<?php


namespace Cocorico\CoreBundle\Admin;


use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\CoreBundle\Form\Type\EqualType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ListingPublicAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'listing_public';
    protected $baseRouteName = 'listing_public';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'certified'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [])
            ->add('title', null, array('label' => 'admin.listing.title.label'))
            ->add('public', null, array('label' => 'admin.listing.public.label'))
            ->add('certified', null, ['editable' => true]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('public', CheckboxType::class)
            ->add('certified', CheckboxType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('certified', null, [
                'advanced_filter' => false,
                'show_filter' => true,
            ]);
    }

    protected function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['certified'] = [
            'type' => EqualType::TYPE_IS_EQUAL,
            'value' => BooleanType::TYPE_NO,
        ];
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }

    public function createQuery($context = 'list')
    {
        $token= $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken();
        $currentUser = $token ? $token->getUser() : null;
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        //COUNTRY_ADMIN can only see users from his country
        if (isset($currentUser) && $currentUser->hasRole("ROLE_COUNTRY_ADMIN")) {

            $query->join($query->getRootAliases()[0] . '.user', 'u')
                ->andWhere(
                    $query->expr()->eq('u.countryOfResidence', ':country')
                )->setParameter(':country', $currentUser->getCountryOfResidence());
        }

        return $query;
    }
}