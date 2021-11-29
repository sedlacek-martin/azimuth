<?php

namespace Cocorico\UserBundle\Controller\Frontend;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Model\UserManager;
use Cocorico\UserBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller managing expiry of user
 *
 * @Route("/expired")
 */
class ExpiredController extends Controller
{

    /**
     * @Route("/", name="cocorico_user_expired")
     * @param Request $request
     * @return Response|null
     */
    public function indexAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $session = $request->getSession();
        $translator = $this->get('translator');

        if (!$user->isExpired()) {
            $session->getFlashBag()->add(
                'warning',
                /** @Ignore */
                $translator->trans('user.expired.not_expired', [], 'cocorico_user')
            );
        }

        return $this->render('CocoricoUserBundle:Frontend/Expired:expired.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/reconfirm", name="cocorico_user_reconfirm")
     * @param Request $request
     * @return RedirectResponse
     */
    public function reconfirmAction(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserManager $userManager */
        $userManager = $this->get('cocorico_user.user_manager');

        if (!$user instanceof User) {
          $token = $request->get('token');
          if ($token) {
            $user = $userManager->getRepository()->findOneByUniqueHash($token);
          }
        }

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $session = $request->getSession();
        $translator = $this->get('translator');

        if ($user->isExpiredSoon(30)) {
            if (!$user->isReconfirmRequested()) {
                $user->setReconfirmRequested(true);
                $user->setReconfirmRequestedAt(new \DateTime());

                $userManager->persistAndFlush($user);


                $session->getFlashBag()->add(
                    'success',
                    /** @Ignore */
                    $translator->trans('reconfirm_request_successful', [], 'cocorico_user')
                );
            } else {
                $session->getFlashBag()->add(
                    'warning',
                    /** @Ignore */
                    $translator->trans('reconfirm_request_already_requested', [], 'cocorico_user')
                );
            }
        } else {
            $session->getFlashBag()->add(
                'error',
                /** @Ignore */
                $translator->trans('user.expired.not_expired', [], 'cocorico_user')
            );
        }

        return $this->redirectToRoute('cocorico_home');
    }

}