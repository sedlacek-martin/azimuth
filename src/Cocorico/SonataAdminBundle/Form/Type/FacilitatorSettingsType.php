<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FacilitatorSettingsType extends AbstractType
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
            ->add('postConfirmation', ChoiceType::class, [
                'choices' => [
                    'facilitator_settings.listing.publish.automatically' => '0',
                    'facilitator_settings.listing.publish.confirmation' => '1',
                ],
                'label' => false,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('messagesConfirmation', ChoiceType::class, [
                'choices' => [
                    'facilitator_settings.messages.automatically' => '0',
                    'facilitator_settings.messages.confirmation' => '1',
                ],
                'label' => false,
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => MemberOrganization::class,
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'facilitator_settings';
    }
}