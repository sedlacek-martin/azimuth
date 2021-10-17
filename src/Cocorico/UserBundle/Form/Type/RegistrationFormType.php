<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Form\Type;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Form\Type\GenderType;
use Cocorico\UserBundle\Entity\User;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Class RegistrationFormType.
 */
class RegistrationFormType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'cocorico_user.tac.error';
    public static $moCountryMismatchError = 'cocorico_user.mo_country_mismatch.error';
    protected $timeUnitIsDay;

    /**
     * RegistrationFormType constructor.
     * @param $timeUnit
     */
    public function __construct($timeUnit)
    {
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                array(
                    'label' => 'form.first_name',
                )
            )
            ->add(
                'lastName',
                TextType::class,
                array(
                    'label' => 'form.last_name',
                )
            )
            ->add(
                'email',
                EmailType::class,
                array('label' => 'form.email')
            )
            ->add(
                'birthday',
                BirthdayType::class,
                array(
                    'label' => 'form.user.birthday',
                    'widget' => 'choice',
                    'years' => range(date('Y') - 13, date('Y') - 100),
                    'required' => true,
                )
            )
            ->add(
                'country',
                CountryType::class,
                array(
                    'placeholder' => '- Select -',
                    'label' => 'form.user.country',
                    'required' => true,
                    'preferred_choices' => array("CZ", "FR", "ES", "DE", "RU"),
                )
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
                array(
                    'type' => 'password',
                    'options' => array('translation_domain' => 'cocorico_user'),
                    'first_options' => array(
                        'label' => 'form.password',
                        'required' => true,
                    ),
                    'second_options' => array(
                        'label' => 'form.password_confirmation',
                        'required' => true,
                    ),
                    'invalid_message' => 'fos_user.password.mismatch',
                    'required' => true,
                )
            )
            ->add('memberOrganization', EntityType::class, [
                'class' => MemberOrganization::class,
                'label' => 'Member organization',
                'placeholder' => '- Select -',
                'choice_attr' => static function (?MemberOrganization $entity) {
                    return is_null($entity) ? [] : ['data-country' => $entity->getCountry()];
                },
            ])
            ->add(
                'tac',
                CheckboxType::class,
                array(
                    'label' => 'listing.form.tac',
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        array(
                            'message' => self::$tacError,
                        )
                    ),
                    'translation_domain' => 'cocorico_listing',
                )
            );

        if (!$this->timeUnitIsDay) {
            $builder
                ->add(
                    'timeZone',
                    TimezoneType::class,
                    array(
                        'label' => 'form.time_zone',
                        'required' => true,
                    )
                );
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {

            /** @var User $user */
                $user = $event->getData();
                $form = $event->getForm();

                if ($user->getMemberOrganization()->getCountry() !== $user->getCountry()) {
                    $form->addError(new FormError(self::$moCountryMismatchError));
                }
                $event->setData($user);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\User',
                'csrf_token_id' => 'user_registration',
                'translation_domain' => 'cocorico_user',
                'validation_groups' => array('CocoricoRegistration'),
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_registration';
    }

    /**
     * JMS Translation messages.
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$tacError, 'cocorico');
        $messages[] = new Message(self::$tacError, 'cocorico');

        return $messages;
    }
}
