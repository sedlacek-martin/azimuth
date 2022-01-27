<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Form\Type\LanguageFilteredType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListingEditDescriptionType extends ListingEditType implements TranslationContainerInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        //Translations fields
        $titles = $descriptions = $rules = [];
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = [
                /* @Ignore */
                'label' => "listing.form.title.$locale",
                'constraints' => [new NotBlank()],
            ];
            $descriptions[$locale] = [
                /* @Ignore */
                'label' => "listing.form.description.$locale",
                'constraints' => [new NotBlank()],
                'config' => [
                    'toolbar' => 'simple1',
                ],

            ];
            $rules[$locale] = [
                /* @Ignore */
                'label' => "listing.form.rules.$locale",
            ];
        }

        $builder
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'required_locales' => [$this->locale],
                    'fields' => [
                        'title' => [
                            'field_type' => 'text',
                            'locale_options' => $titles,
                        ],
                        'description' => [
                            'field_type' => CKEditorType::class,
                            'locale_options' => $descriptions,
                        ],
                        'rules' => [
                            'field_type' => 'textarea',
                            'locale_options' => $rules,
                        ],
                        'slug' => [
                            'display' => false,
                        ],
                    ],
                    /* @Ignore */
                    'label' => false,
                ]
            )
            ->add(
                'fromLang',
                LanguageFilteredType::class,
                [
                    'mapped' => false,
                    'label' => 'cocorico.from',
                    'data' => $this->locale,
                    'translation_domain' => 'cocorico_user',
                ]
            )
            ->add(
                'toLang',
                LanguageFilteredType::class,
                [
                    'mapped' => false,
                    'label' => 'cocorico.to',
                    'data' => LanguageFilteredType::getLocaleTo($this->locales, $this->locale),
                    'translation_domain' => 'cocorico_user',
                ]
            );

        //Status field already added
        //$builder->remove('status');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit_description';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message('listing.form.title.en', 'cocorico_listing');
        $messages[] = new Message('listing.form.title.fr', 'cocorico_listing');
        $messages[] = new Message('listing.form.description.en', 'cocorico_listing');
        $messages[] = new Message('listing.form.description.fr', 'cocorico_listing');
        $messages[] = new Message('listing.form.rules.en', 'cocorico_listing');
        $messages[] = new Message('listing.form.rules.fr', 'cocorico_listing');
        $messages[] = new Message('cocorico.en', 'cocorico_listing');
        $messages[] = new Message('cocorico.fr', 'cocorico_listing');
        $messages[] = new Message('listing_translations_en_title_placeholder', 'cocorico_listing');
        $messages[] = new Message('listing_translations_fr_title_placeholder', 'cocorico_listing');
        $messages[] = new Message('listing_translations_en_description_placeholder', 'cocorico_listing');
        $messages[] = new Message('listing_translations_fr_description_placeholder', 'cocorico_listing');
        $messages[] = new Message('listing_translations_en_rules_placeholder', 'cocorico_listing');
        $messages[] = new Message('listing_translations_fr_rules_placeholder', 'cocorico_listing');

        return $messages;
    }
}
