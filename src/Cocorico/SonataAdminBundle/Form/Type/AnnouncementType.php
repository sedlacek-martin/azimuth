<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\CoreBundle\Entity\Announcement;
use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Utils\ElFinderHelper;
use Doctrine\ORM\EntityManager;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType
{
    /** @var string */
    protected $rootDir;

    private $request;

    private $locale;

    private $entityManager;

    /**
     * @param RequestStack  $requestStack
     * @param EntityManager $entityManager
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
            ->add('heading', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 50,
                    'class' => 'form-control',
                ],
            ])
            ->add('shortDescription', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 150,
                    'class' => 'form-control',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'required' => true,
                'config' => [
                    'filebrowserBrowseRoute' => 'elfinder',
                    'filebrowserBrowseRouteParameters' => [
                        'instance' => 'ckeditor',
                        'homeFolder' => ElFinderHelper::getOrCreateFolder(ElFinderHelper::GLOBAL_DIR, $this->rootDir),
                    ],
                ],
            ])
            ->add('showAt', DateTimeType::class, [
                'data' => new \DateTime(),
            ])
            ->add('memberOrganizations', EntityType::class, [
                'class' => MemberOrganization::class,
                'required' => false,
                'multiple' => true,
                'placeholder' => 'All organizations',
                'mapped' => false,
            ])
            ->add('includeAdmins', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => Announcement::class,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'announcement_new';
    }
}
