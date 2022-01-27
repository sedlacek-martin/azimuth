<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\ContactBundle\Entity\Contact;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactReplyType extends AbstractType
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
            ->add('reply', TextareaType::class, [
                'label' => 'admin.contact.reply.label',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => Contact::class,
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'admin_contact_reply';
    }
}
