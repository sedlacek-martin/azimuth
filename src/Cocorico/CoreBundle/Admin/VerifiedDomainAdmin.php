<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VerifiedDomainAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'verified-domain';
    protected $baseRouteName = 'verified-domain';
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
            ->add('domain', null, array())
            ->add('memberOrganization', null, array());

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
            ->add('domain', TextType::class)
            ->add('memberOrganization', null, [
                'required' => true,
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('memberOrganization', null,  []);
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

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.memberOrganization', 'mo')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }


}