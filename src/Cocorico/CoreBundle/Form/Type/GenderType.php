<?php

namespace Cocorico\CoreBundle\Form\Type;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'user.gender.male' => User::MALE,
                'user.gender.female' => User::FEMALE,
                'user.gender.other' => User::OTHER,
            ],
            'label' => 'form.gender',
            'translation_domain' => 'cocorico_user',
            'attr' => ['class' => 'no-arrow'],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'gender';
    }
}
