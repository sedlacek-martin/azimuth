<?php

namespace Cocorico\ContactBundle\Admin;

use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContactCategoryAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'contact-category';

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', null, [])
            ->add('subject', null, [])
            ->add('uri', null, [])
            ->add('public', null, [])
            ->add('allowSubject', null, [])
            ->add('recipientRoles', ChoiceType::class, [
                'choices' => [
                    'role.super_admin' => 'ROLE_SUPER_ADMIN',
                    'role.facilitator' => 'ROLE_FACILITATOR',
                    'role.activator' => 'ROLE_ACTIVATOR',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, [])
            ->add('uri', null, [])
            ->add('public', null, [])
            ->add('allowSubject', null, [])
            ->add('recipientRoleNames', null, [
                'label' => 'admin.contact.recipient_roles.label',
                'template' => 'CocoricoSonataAdminBundle::list_field_array.html.twig',
                'data_trans' => 'SonataAdminBundle',
            ]);

        $list->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                ],
            ]
        );
    }
}
