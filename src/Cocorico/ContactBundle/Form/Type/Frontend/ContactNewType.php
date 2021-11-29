<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Form\Type\Frontend;

use Cocorico\ContactBundle\Entity\ContactCategory;
use Cocorico\ContactBundle\Repository\ContactCategoryRepository;
use Cocorico\UserBundle\Entity\User;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ContactNewType extends AbstractType implements TranslationContainerInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $options['user'];

        if (!$user instanceof User) {
            $builder
                ->add(
                    'firstName',
                    null,
                    array(
                        'label' => 'contact.form.first_name.label',
                        'data' => $user ? $user->getFirstName() : '',
                        'attr' => [
                            'data-type' => 'first-name'
                        ]
                    )
                )
                ->add(
                    'lastName',
                    null,
                    array(
                        'label' => 'contact.form.last_name.label',
                        'data' => $user ? $user->getLastName() : '',
                        'attr' => [
                            'data-type' => 'last-name'
                        ]
                    )
                )
                ->add(
                    'email',
                    EmailType::class,
                    array(
                        'label' => 'contact.form.email.label',
                        'data' => $user ? $user->getEmail() : '',
                        'attr' => [
                            'data-type' => 'email'
                        ]
                    )

                );
        }

        $builder
            ->add('category',
            EntityType::class,
            [
                'label' => 'contact.form.category.label',
                'class' => ContactCategory::class,
                'required' => true,
                'placeholder' => '- Select -',
                'query_builder' => function (ContactCategoryRepository $repository) use ($options)  {
                    return $repository->createQueryBuilder('qb')
                        ->andWhere('qb.public = :public')
                        ->setParameter('public',$options['public'] );
                },
                'choice_attr' => function(ContactCategory $choice, $key, $value) {
                    return [
                        'is-public' => $choice->isPublic() ? 'true' : 'false',
                        'allow-subject' => $choice->isAllowSubject() ? 'true' : 'false',
                        'data-subject' => $choice->getSubject(),
                    ];
                },
                'attr' => [
                    'data-type' => 'category'
                ]
            ])
            ->add(
                'subject',
                null,
                array(
                    'label' => 'contact.form.subject.label',
                    'attr' => [
                        'data-type' => 'subject'
                    ]
                )
            )
            ->add(
                'message',
                null,
                array(
                    'label' => 'contact.form.message.label'
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\ContactBundle\Entity\Contact',
                'translation_domain' => 'cocorico_contact',
                'constraints' => new Valid(),
                'validation_groups' => array('CocoricoContact'),
                'allow_extra_fields' => true,
                'public' => true,
                'user' => null,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'contact_new';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("entity.contact.status.new", 'cocorico_contact');
        $messages[] = new Message("entity.contact.status.read", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.first_name.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.first_name.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.first_name.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.last_name.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.last_name.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.last_name.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.email.invalid", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.email.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.email.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.email.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.phone.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.phone.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.subject.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.subject.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.subject.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.message.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.message.short", 'cocorico_contact');

        return $messages;
    }
}
