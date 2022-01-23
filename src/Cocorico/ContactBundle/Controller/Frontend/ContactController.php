<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Controller\Frontend;

use Cocorico\ContactBundle\Entity\Contact;
use Cocorico\ContactBundle\Form\Type\Frontend\ContactNewType;
use Cocorico\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

/**
 * Booking controller.
 *
 * @Route("/contact")
 */
class ContactController extends Controller
{
    /**
     * Creates a new Contact entity.
     *
     * @Route("/new", name="cocorico_contact_new")
     *
     * @Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction()
    {
        $contact = new Contact();
        /** @var User $user */
        $user = $this->getUser();

        if ($user !== null) {
            $contact
                ->setEmail($user->getEmail())
                ->setUser($user)
                ->setFirstName($user->getFirstName())
                ->setLastName($user->getLastName());
        }

        $form = $this->createCreateForm($contact, [
            'public' => !$user instanceof User,
            'user' => $user,
        ]);

        $submitted = $this->get('cocorico_contact.form.handler.contact')->process($form, $user);
        if ($submitted !== false) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('contact.new.success', [], 'cocorico_contact')
            );

            return $this->redirect($this->generateUrl('cocorico_contact_new'));
        }

        return $this->render(
            'CocoricoContactBundle:Frontend:index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Creates a form to create a contact entity.
     *
     * @param Contact $contact The entity
     *
     * @return Form The form
     */
    private function createCreateForm(Contact $contact, array $options = [])
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ContactNewType::class,
            $contact,
            array_merge([
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_contact_new'),
            ], $options)
        );

        return $form;
    }
}
