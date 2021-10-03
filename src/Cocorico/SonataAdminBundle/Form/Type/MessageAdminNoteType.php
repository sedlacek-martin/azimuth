<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use Cocorico\MessageBundle\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageAdminNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adminNote', TextareaType::class, [
                'label' => 'message.validation.admin_note.label',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => Message::class,
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'message_admin_note';
    }


}