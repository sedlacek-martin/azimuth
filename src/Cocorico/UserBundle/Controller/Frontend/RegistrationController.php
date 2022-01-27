<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Controller\Frontend;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Form\Type\RegistrationAdditionalInfoFormType;
use Cocorico\UserBundle\Form\Type\RegistrationFormType;
use Cocorico\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Class RegistrationController
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="cocorico_user_register")
     *
     * @param Request $request
     *
     * @return Response
     * @throws AuthenticationCredentialsNotFoundException
     * @throws \RuntimeException
     */
    public function registerAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('cocorico_home');
        }

        $email = $request->get('email');

        /** @var UserManager $userManager */
        $userManager = $this->get('cocorico_user.user_manager');
        $user = $userManager->createUser();
        // prefill email if contained in url parameter
        if ($email) {
            $user->setEmail($email);
        }
        $form = $this->createCreateForm($user);
        $confirmation = $this->getParameter('cocorico.registration_confirmation');

        $process = $this->get('cocorico_user.form.handler.registration')->process($form, $confirmation);
        if ($process) {
            /** @var User $user */
            $user = $form->getData();

            if ($confirmation) {
                // email confirmation needed
                $this->get('session')->set('cocorico_user_send_confirmation_email/email', $user->getEmail());
                if ($user->getMemberOrganization()->isRequiresUserIdentifier() && !$user->isTrusted()) {
                    // additional info required
                    $url = $this->get('router')->generate('cocorico_user_register_additional_info', ['hash' => $user->getUniqueHash()]);
                } else {
                    // additional info not needed or user is already trusted
                    $url = $this->get('router')->generate('cocorico_user_registration_check_email');
                }
            } else {
                // confirmation is not needed
                $url = $request->get('redirect_to') ?? $this->get('router')->generate('cocorico_user_register_confirmed');
            }

            return new RedirectResponse($url);
        }

        return $this->render('CocoricoUserBundle:Frontend/Registration:register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a Registration form
     *
     * @param User $user The entity
     *
     * @return Form The form
     */
    private function createCreateForm(User $user): Form
    {
        return $this->get('form.factory')->createNamed(
            'user_registration',
            RegistrationFormType::class,
            $user
        );
    }

    /**
     * Tell the user to check their email provider.
     *
     * @Route("/check-email", name="cocorico_user_registration_check_email")
     * @Method("GET")
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('cocorico_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->get('router')->generate('cocorico_user_register'));
        }

        $this->get('session')->remove('cocorico_user_send_confirmation_email/email');
        $user = $this->get('cocorico_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('CocoricoUserBundle:Frontend/Registration:checkEmail.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Receive the confirmation token from user email provider, login the user.
     *
     * @Route("/register-confirmation/{token}", name="cocorico_user_register_confirmation")
     * @Method("GET")
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function confirmAction(Request $request, string $token)
    {
        /** @var User $user */
        $user = $this->get('cocorico_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEmailVerified(true);
        $user->setLastLogin(new \DateTime());

        // reset the session
        $this->get('session')->remove('cocorico_user_need_verification');

        $this->get('cocorico_user.form.handler.registration')->handleRegistration($user);

        return new RedirectResponse($this->get('router')->generate('cocorico_user_register_confirmed'));
    }

    /**
     * Tell the user his account is now confirmed.
     *
     * @Route("/register-confirmed", name="cocorico_user_register_confirmed")
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function confirmedAction(): Response
    {
        $needVerification = $this->get('session')->get('cocorico_user_need_verification', false);

        if ($needVerification) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('registration.email_verified.success', [], 'cocorico_user')
            );
            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans('registration.needVerification', [], 'cocorico_user')
            );
            $this->get('session')->remove('cocorico_user_need_verification');
        }

        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render(
            'CocoricoUserBundle:Frontend/Registration:confirmed.html.twig',
            [
                'user' => $user,
                'targetUrl' => $this->getTargetUrlFromSession(),
                'needVerification' => $needVerification,
            ]
        );
    }

    /**
     * Requires additional registration info from user
     *
     * @Route("/additional-info/{hash}", name="cocorico_user_register_additional_info")
     */
    public function additionalRegistrationInfoAction(string $hash, Request $request): ?Response
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('cocorico_user.user_manager');

        $user = $userManager->getRepository()->findOneByUniqueHash($hash);

        if ($user === null || !$user->getMemberOrganization()->isRequiresUserIdentifier()) {
            throw $this->createNotFoundException();
        }

        if ($user->getOrganizationIdentifier() !== null) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans('registration.additional_info.already_filled', [], 'cocorico_user')
            );

            return new RedirectResponse($this->get('router')->generate('cocorico_user_login'));
        }

        /** @var FormFactory $formFactory */
        $formFactory = $this->get('form.factory');
        $form = $formFactory
            ->createNamed('user_registration_additional_info', RegistrationAdditionalInfoFormType::class, $user, [])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('registration.additional_info.success', [], 'cocorico_user')
            );

            return new RedirectResponse($this->get('router')->generate('cocorico_user_login'));
        }

        return $this->render('CocoricoUserBundle:Frontend/Registration:additionalInfo.html.twig', [
            'form' => $form->createView(),
            'userIdentifierDescription' => $user->getMemberOrganization()->getUserIdentifierDescription() ?? '',
        ]);
    }

    /**
     * @return mixed
     */
    private function getTargetUrlFromSession()
    {
        $key = sprintf('_security.%s.target_path', $this->get('security.token_storage')->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }

        return null;
    }
}
