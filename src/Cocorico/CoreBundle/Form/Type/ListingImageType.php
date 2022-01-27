<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ListingImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                HiddenType::class,
                [
                    /* @Ignore */
                    'label' => false,
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'image_path' => 'webPath',
                    'imagine_filter' => 'listing_xxmedium',
                    /* @Ignore */
                    'label' => false,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'dn',
                    ],
                ]
            )
            ->add(
                'position',
                HiddenType::class,
                [
                    /* @Ignore */
                    'label' => false,
                    'attr' => [
                        'class' => 'sort-position',
                    ],
                ]
            )
            ->add(
                'listing',
                EntityHiddenType::class,
                [
                    'class' => 'Cocorico\CoreBundle\Entity\Listing',
                    /* @Ignore */
                    'label' => false,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Cocorico\CoreBundle\Entity\ListingImage',
                'csrf_token_id' => 'listing_image',
                'translation_domain' => 'cocorico_listing',
                'constraints' => new Valid(),
                /* @Ignore */
                'label' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_image';
    }
}
