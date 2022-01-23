<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Form\Type;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Form\Type\PriceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfileScoutInfoFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scoutName', TextType::class, [
                    'label' => 'form.user.scout_name',
            ])
            ->add('scoutSince', ChoiceType::class, [
                'choices' => array_combine(
                    range(date('Y'), date('Y') - 100),
                    range(date('Y'), date('Y') - 100)
                ),
                'label' => 'form.user.scout_since',
            ])
            ->add('memberOrganization', EntityType::class, [
                'class' => MemberOrganization::class,
                'disabled' => true,
                'label' => 'form.user.member_organization',
                'placeholder' => '- Select -',
                'choice_attr' => static function (?MemberOrganization $entity) {
                    return is_null($entity) ? [] : ['data-country' => $entity->getCountry()];
                },
            ])
            ->add('country', CountryType::class, [
                    'disabled' => true,
                    'label' => 'form.user.country',
                    'preferred_choices' => ['CZ', 'FR', 'ES', 'DE', 'RU'],
            ])
            ->add('location', TextType::class, [
                'label' => 'form.user.location',
            ]);
//            ->add(
//                'firstName',
//                TextType::class,
//                array(
//                    'label' => 'form.user.first_name'
//                )
//            )
//            ->add(
//                'country',
//                CountryType::class,
//                array(
//                    'label' => 'form.user.country',
//                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
//                )
//            )
//            ->add(
//                'countryOfResidence',
//                CountryType::class,
//                array(
//                    'label' => 'form.user.country_of_residence',
//                    'required' => true,
//                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
//                )
//            )
//            ->add(
//                'profession',
//                TextType::class,
//                array(
//                    'label' => 'form.user.profession',
//                    'required' => false
//                )
//            )
//            ->add(
//                'annualIncome',
//                PriceType::class,
//                array(
//                    'label' => 'form.user.annual_income',
//                    'translation_domain' => 'cocorico_user',
//                    'required' => false
//                )
//            )
//            ->add(
//                'bankOwnerName',
//                TextType::class,
//                array(
//                    'label' => 'form.user.bank_owner_name',
//                    'required' => true,
//                )
//            )
//            ->add(
//                'bankOwnerAddress',
//                TextareaType::class,
//                array(
//                    'label' => 'form.user.bank_owner_address',
//                    'required' => true,
//                )
//            )
//            ->add(
//                'iban',
//                TextType::class,
//                array(
//                    'label' => 'IBAN',
//                    'required' => true
//                )
//            )
//            ->add(
//                'bic',
//                TextType::class,
//                array(
//                    'label' => 'BIC',
//                    'required' => true
//                )
//            );
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Cocorico\UserBundle\Entity\User',
                'csrf_token_id' => 'CocoricoProfileScoutInfo',
                'translation_domain' => 'cocorico_user',
                'constraints' => new Valid(),
                'validation_groups' => ['CocoricoProfileScoutInfo'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_profile_scout_info';
    }
}
