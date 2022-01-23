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

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingCategoryType extends AbstractType
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
        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $categories = $this->entityManager->getRepository('CocoricoCoreBundle:ListingCategory')->findCategories(
            $this->locale
        );

        $resolver
            ->setDefaults(
                [
                    'class' => ListingCategory::class,
                    'choices' => $categories,
                    'multiple' => false,
                    'required' => false,
                    'placeholder' => null,
                    /* @Ignore */
                    'label' => false,
                    'choice_attr' => static function (?ListingCategory $choice) {
                        if (is_null($choice)) {
                            return [];
                        }
                        $characteristics = $choice->getCharacteristics()->map(function (ListingCharacteristic $char) {
                            return $char->getId();
                        })->toArray();

                        return [
                            'data-offer' => $choice->isOffer() ? 'true' : 'false',
                            'data-search' => $choice->isSearch() ? 'true' : 'false',
                            'data-characteristics' => json_encode($characteristics),
                        ];
                    },
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_category';
    }
}
