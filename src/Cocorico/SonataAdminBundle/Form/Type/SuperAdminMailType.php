<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperAdminMailType extends AbstractType
{
    public const ROLE_CHOICES = [
        'super_admin_actions.emails_filter.role_facilitator.label' => 'ROLE_FACILITATOR',
        'super_admin_actions.emails_filter.role_activator.label' => 'ROLE_ACTIVATOR',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'label' => 'super_admin_actions.emails_filter.label',
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'choices' => self::ROLE_CHOICES,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'super_admin_actions_email_role_filter';
    }
}