<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatisticsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', TextType::class, [
                'label' => 'listing.form.expiry_date.label',
                'attr' => [
                    'class' => 'form-control datepicker',
                ]
            ])
            ->add('to', TextType::class, [
                'label' => 'listing.form.expiry_date.label',
                'attr' => [
                    'class' => 'form-control datepicker',
                ]
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
        return 'statistics_filter';
    }


}