<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;

class AnnouncementAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'announcement';

    protected $baseRouteName = 'announcement';

    protected $locales;

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [])
            ->add('heading', null, [])
            ->add('shortDescription', null, [
                'truncate' => [
                    'length' => 50,
                ],
            ])
            ->add('content', 'html', [
                'truncate' => [
                    'length' => 150,
                    'preserve' => true,
                ],
            ])
            ->add('showAt', null, [])
            ->add('createdAt', null, [])
            ->add('usersCount', null, []);

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'delete' => [],
                    'edit' => [],
                    'show_users' => [
                        'template' => 'CocoricoSonataAdminBundle::list_action_announcement_show_users.html.twig',
                    ],
                ],
            ]
        );
    }
}
