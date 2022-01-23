<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Form\Type\EntityHiddenType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Message form type for starting a new conversation
 */
class NewThreadMessageFormType extends AbstractType implements TranslationContainerInterface
{
    public static $messageError = 'message.body.not_blank';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'listing',
                EntityHiddenType::class,
                ['class' => 'Cocorico\CoreBundle\Entity\Listing']
            )
            ->add(
                'recipient',
                EntityHiddenType::class,
                ['class' => 'Cocorico\UserBundle\Entity\User']
            )
            ->add(
                'subject',
                HiddenType::class,
                ['data' => 'Contact']
            )
            ->add('fromDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'message_form.from_date.title',
                'format' => 'dd/MM/yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('toDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'message_form.from_date.title',
                'format' => 'dd/MM/yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add(
                'body',
                TextareaType::class,
                [
                    /* @Ignore */
                    'label' => false,
                    'attr' => ['rows' => '6'],
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => self::$messageError]),
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
//                'data_class' => 'Cocorico\MessageBundle\Entity\Thread',
                'csrf_token_id' => 'message',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'fos_message_new_thread';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = [];
        $messages[] = new Message(self::$messageError, 'cocorico');

        return $messages;
    }
}
