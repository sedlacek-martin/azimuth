<?php

namespace Cocorico\UserBundle\Controller\Admin;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Booking Dashboard controller.
 *
 * @Route("/admin/user/action/")
 */
class CustomUserActionController extends Controller
{
    /**
     * @Route("trusted/{id}", name="cocorico_admin__trusted")
     * @Method("GET")
     */
    public function trustedAction(User $user): RedirectResponse
    {
        /** @var TwigSwiftMailer $mailer */
        $mailer = $this->container->get('cocorico_user.mailer.twig_swift');
        $em = $this->getDoctrine()->getManager();
        if (!$user->isTrusted()) {
            try {
                $user->setTrusted(true);
                if (!$user->isTrustedEmailSent()) {
                    $mailer->sendAccountTrusted($user);
                    $user->setTrustedEmailSent(true);
                }
                $em->persist($user);
                $this->addFlash(
                    'sonata_flash_success',
                    $this->get('translator')->trans(
                        'flash_action_trusted_success',
                        array(),
                        'SonataAdminBundle')
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->get('translator')->trans(
                        'flash_action_trusted_error',
                        array(),
                        'SonataAdminBundle')
                );
            }
        } else if ($user->isExpired() && $user->isReconfirmRequested()) {
            try {

                $extensionPeriod = $user->getMemberOrganization()->getUserExpiryPeriod();
                $newExpiryDate = (new \DateTime())->add(new \DateInterval("P{$extensionPeriod}D"));
                $user->setExpiryDate($newExpiryDate);
                $user->setReconfirmRequested(false);
                $user->setReconfirmRequestedAt(null);
                $mailer->sendAccountReactivated($user);
                $em->persist($user);

                $this->addFlash(
                    'sonata_flash_success',
                    $this->get('translator')->trans(
                        'flash_action_user_reconfirm_success',
                        array(),
                        'SonataAdminBundle')
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->get('translator')->trans(
                        'flash_action_user_reconfirm_error',
                        array(),
                        'SonataAdminBundle')
                );
            }
        }
        $em->flush();

        return new RedirectResponse(
            $this->generateUrl('verification_list')
        );
    }
}
