<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Doctrine\ORM\EntityManager;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoEditType extends AbstractType
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
            ->add('abstract', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 150,
                    'class' => 'form-control'
                ]
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'MO full description',
                'required' => false,
            ])
            ->add('countryDescription', CKEditorType::class, [
                'mapped' => false,
                'label' => 'Country description',
                'required' => false,
                'data' => $options['country_description'],
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
                'country_description' => ''
            ]);
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'edit_mo';
    }
}