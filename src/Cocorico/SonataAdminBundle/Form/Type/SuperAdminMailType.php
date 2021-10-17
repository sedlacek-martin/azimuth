<?php

namespace Cocorico\SonataAdminBundle\Form\Type;

use JMS\TranslationBundle\Model\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperAdminMailType extends AbstractType
{
    protected static $facilitatorLabel = 'super_admin_actions.emails_filter.role_facilitator.label';
    protected static $activatorLabel = 'super_admin_actions.emails_filter.role_activator.label';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'label' => 'super_admin_actions.emails_filter.label',
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'choices' => [
                    self::$facilitatorLabel => 'ROLE_FACILITATOR',
                    self::$activatorLabel => 'ROLE_ACTIVATOR',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'SonataAdminBundle',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'super_admin_actions_email_role_filter';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$facilitatorLabel, 'SonataAdminBundle');
        $messages[] = new Message(self::$activatorLabel, 'SonataAdminBundle');

        return $messages;
    }
}