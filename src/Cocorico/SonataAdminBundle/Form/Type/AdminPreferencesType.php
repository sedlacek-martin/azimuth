<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminPreferencesType extends AbstractType
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
            ->add('disableAdminNotifications', CheckboxType::class, [
                'label' => 'preferences.disable_notifications',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => User::class,
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'admin_preferences';
    }
}
