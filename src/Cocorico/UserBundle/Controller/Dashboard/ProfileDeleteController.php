<?php

namespace Cocorico\UserBundle\Controller\Dashboard;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Form\Type\PasswordCheckFormType;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Cocorico\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller managing the user profile
 *
 * @Route("/user")
 */
class ProfileDeleteController extends Controller
{

    /**
     * Delete user profile
     *
     * @Route("/delete", name="cocorico_user_dashboard_profile_delete")
     * @Method({"GET", "POST"})
     *
     * @param $request Request
     */
    public function deleteAction(Request $request)
    {
        $success = false;
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');

        $form = $this->createForm(PasswordCheckFormType::class, null, [
            'action' => $this->generateUrl('cocorico_user_dashboard_profile_delete'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->getData()['password'];
            /** @var EncoderFactory $encoderFactory */
            $encoderFactory = $this->get('security.encoder_factory');
            $encoder = $encoderFactory->getEncoder($user);

            $passwordIsValid = $encoder->isPasswordValid(
                $user->getPassword(),
                $password,
                $user->getSalt()
            );

            if ($passwordIsValid) {
                /** @var TwigSwiftMailer $mailer */
                $mailer = $this->get('cocorico_user.mailer.twig_swift');
                $mailer->sendAccountDeleted($user);
                $this->addFlash(
                    'success',
                    'OK'
                );
                $this->get('security.token_storage')->setToken(null);
                $request->getSession()->invalidate();

                $em->remove($user);
                $em->flush();

                $success = true;

            } else {
                $this->addFlash(
                    'error',
                    $translator->trans('user.delete.invalid_password.error', array(), 'cocorico_user')
                );
            }
        }

        return $this->render(
            'CocoricoUserBundle:Dashboard/Profile:delete_profile.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
                'deleteSuccess' => $success,
            )
        );
    }
}