<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivatorSettingsType extends AbstractType
{
    private $request;
    private $locale;
    private $entityManager;

    /**
     * @param RequestStack  $requestStack
     * @param EntityManager $entityManager
     */
    public function __construct(RequestStack $requestStack, EntityManager $entityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userExpiryPeriod', IntegerType::class, [
                'label' => 'activator_settings.user.expiry_period',
                'required' => false,
            ])
            ->add('registrationAcceptDomain', CheckboxType::class, [
                'label' => 'activator_settings.user.registration.verified_domains',
                'required' => false,
            ])
            ->add('registrationAcceptActivation', CheckboxType::class, [
                'label' => 'activator_settings.user.registration.activation',
                'required' => false
            ])
            ->add('requiresUserIdentifier', CheckboxType::class, [
                'label' => 'activator_settings.user.registration.user_identifier_required.label',
                'required' => false,
            ])
            ->add('userIdentifierDescription', TextType::class, [
                'label' =>  'activator_settings.user.registration.user_identifier_description.label',
                'required' => false,
                'attr' => [
                    'size' => '80'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => MemberOrganization::class,
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'activator_settings';
    }
}