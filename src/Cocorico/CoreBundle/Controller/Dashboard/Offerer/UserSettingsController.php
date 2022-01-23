<?php

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Form\Type\Dashboard\UserSettingsType;
use Cocorico\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing Dashboard category controller.
 *
 * @Route("/settings")
 */
class UserSettingsController extends Controller
{
    public function createUserSettingForm(User $user)
    {
        return $this
            ->get('form.factory')
            ->createNamed('user_settings', UserSettingsType::class, $user, [
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_dashboard_edit_user_settings'),
            ]);
    }

    /**
     * Edit Listing categories.
     *
     * @Route("/", name="cocorico_dashboard_edit_user_settings")
     *
     * @Method({"POST", "GET"})
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editUserSettingsAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createUserSettingForm($user);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
        if ($formIsValid) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('user_settings.edit.success', [], 'cocorico_user')
            );

            return $this->redirectToRoute('cocorico_dashboard_edit_user_settings');
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Settings:edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}
