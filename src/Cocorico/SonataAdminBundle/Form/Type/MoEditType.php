<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Utils\ElFinderHelper;
use Doctrine\ORM\EntityManager;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoEditType extends AbstractType
{
    /**
     * @var string
     */
    protected $rootDir;
    private $request;
    private $locale;
    private $entityManager;

    /**
     * @param RequestStack $requestStack
     * @param EntityManager $entityManager
     * @param string $rootDir
     */
    public function __construct(RequestStack $requestStack, EntityManager $entityManager, string $rootDir)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->entityManager = $entityManager;
        $this->rootDir = $rootDir;
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
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $mo = $event->getData();

            if (!$mo instanceof MemberOrganization) {
                return;
            }

            $form
                ->add('description', CKEditorType::class, [
                    'label' => 'MO full description',
                    'required' => false,
                    'config' => [
                        'filebrowserBrowseRoute' => 'elfinder',
                        'filebrowserBrowseRouteParameters' => [
                            'instance' => 'ckeditor',
                            'homeFolder' => ElFinderHelper::getOrCreateFolder($mo->getCountry(), $this->rootDir)
                        ]
                    ]
                ])
                ->add('countryDescription', CKEditorType::class, [
                    'mapped' => false,
                    'label' => 'Country description',
                    'required' => false,
                    'data' => $options['country_description'],
                    'config' => [
                        'filebrowserBrowseRoute' => 'elfinder',
                        'filebrowserBrowseRouteParameters' => [
                            'instance' => 'ckeditor',
                            'homeFolder' => ElFinderHelper::getOrCreateFolder($mo->getCountry(), $this->rootDir)
                        ]
                    ]
                ]);
        });
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