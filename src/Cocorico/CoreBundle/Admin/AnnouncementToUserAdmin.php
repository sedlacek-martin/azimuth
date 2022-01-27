<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class AnnouncementToUserAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'user-announcement';

    protected $baseRouteName = 'user-announcement';

    protected $locales;

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [])
            ->add('user', null, [])
            ->add('announcement', null, [])
            ->add('displayed', null, [])
            ->add('dismissed', null, [])
            ->add('displayedAt', 'datetime', [])
            ->add('dismissedAt', 'datetime', []);

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'delete' => [],
                    'edit' => [],
                ],
            ]
        );
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('announcement', null, [])
            ->add('user', null, [])
            ->add('displayed', null, [])
            ->add('dismissed', null, []);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->remove('create');
    }
}
