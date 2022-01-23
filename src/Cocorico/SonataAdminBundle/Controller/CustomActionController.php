<?php

namespace Cocorico\SonataAdminBundle\Controller;

use Cocorico\ContactBundle\Entity\Contact;
use Cocorico\ContactBundle\Model\BaseContact;
use Cocorico\ContactBundle\Repository\ContactRepository;
use Cocorico\CoreBundle\Entity\CountryInformation;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Entity\UserInvitation;
use Cocorico\CoreBundle\Repository\CountryInformationRepository;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\CoreBundle\Security\Voter\BaseVoter;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\MessageBundle\Event\MessageEvent;
use Cocorico\MessageBundle\Event\MessageEvents;
use Cocorico\MessageBundle\Repository\MessageRepository;
use Cocorico\SonataAdminBundle\Form\Type\ActivatorSettingsType;
use Cocorico\SonataAdminBundle\Form\Type\AdminPreferencesType;
use Cocorico\SonataAdminBundle\Form\Type\ContactReplyType;
use Cocorico\SonataAdminBundle\Form\Type\FacilitatorSettingsType;
use Cocorico\SonataAdminBundle\Form\Type\MessageAdminNoteType;
use Cocorico\SonataAdminBundle\Form\Type\MoEditType;
use Cocorico\SonataAdminBundle\Form\Type\SuperAdminMailType;
use Cocorico\SonataAdminBundle\Form\Type\TestMailType;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Cocorico\UserBundle\Repository\UserRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/action/")
 */
class CustomActionController extends Controller
{
    /**
     * @Route("", name="cocorico_admin__index")
     * @Method("GET")
     */
    public function indexAction()
    {
    }

    /**
     * @Route("dashboard", name="cocorico_admin__dashboard")
     * @Method("GET")
     */
    public function dashboardAction(): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);
        /** @var ListingRepository $listingRepository */
        $listingRepository = $em->getRepository(Listing::class);
        /** @var MessageRepository $messageRepository */
        $messageRepository = $em->getRepository(Message::class);
        /** @var ContactRepository $contactRepository */
        $contactRepository = $em->getRepository(Contact::class);

        $moId = null;
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $moId = $currentUser->getMemberOrganization()->getId();
        }

        $waitingActivationCount = $userRepository->getWaitingActivationCount($moId);
        $postToValidateCount = $listingRepository->getWaitingForValidationCount($moId);
        $messagesToVerify = $messageRepository->getWaitingForValidationCount($moId);
        $facilitatorContact = $contactRepository->getCountNewByRole('ROLE_FACILITATOR', $moId);
        $activatorContact = $contactRepository->getCountNewByRole('ROLE_ACTIVATOR', $moId);

        return $this->render(
            'CocoricoSonataAdminBundle::CustomActions/dashboard.html.twig',
            [
                'activationCount' => $waitingActivationCount,
                'validatePostCount' => $postToValidateCount,
                'validateMessageCount' => $messagesToVerify,
                'facilitatorContactCount' => $facilitatorContact,
                'activatorContactCount' => $activatorContact,
            ]
        );
    }

    /**
     * @Route("toggle-super-admin", name="cocorico_admin__toggle_super_admin")
     * @Method("GET")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleSuperAdminMenuAction(Request $request): JsonResponse
    {
        $session = $this->get('session');
        if ($request->query->get('opened-super-admin-menu') !== null) {
            $session->set('opened-super-admin-menu', $request->query->getBoolean('opened-super-admin-menu', false));
        }

        return new JsonResponse(['hide_sidebar' => $session->get('opened-super-admin-menu', false)]);
    }

    /**
     * @Route("edit-mo/{id}", name="cocorico_admin__edit_mo")
     * @Method({"GET", "POST"})
     */
    public function editMOInfoAction(MemberOrganization $memberOrganization, Request $request): ?Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_FACILITATOR')) {
            throw $this->createAccessDeniedException('You are not allowed to MO content');
        }

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();
            if ($user->getMemberOrganization()->getId() !== $memberOrganization->getId()) {
                throw $this->createAccessDeniedException('You are not allowed to edit this MO');
            }
        }

        $em = $this->getDoctrine()->getManager();
        /** @var CountryInformationRepository $countryInfoRepository */
        $countryInfoRepository = $em->getRepository(CountryInformation::class);
        $countryInfo = $countryInfoRepository->findByCountryCode($memberOrganization->getCountry());

        $form = $this->createForm(MoEditType::class, $memberOrganization, [
            'action' => $this->generateUrl('cocorico_admin__edit_mo', ['id' => $memberOrganization->getId()]),
            'country_description' => $countryInfo ? $countryInfo->getDescription() : '',
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($countryInfo === null) {
                $countryInfo = new CountryInformation();
                $countryInfo->setCountry($memberOrganization->getCountry());
            }
            $countryInfo->setDescription($form->get('countryDescription')->getData());

            $em->persist($memberOrganization);
            $em->persist($countryInfo);
            $em->flush();
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/edit_mo.html.twig', [
                'memberOrganization' => $memberOrganization,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("activator/settings", name="cocorico_admin__activator_settings")
     * @Method({"GET", "POST"})
     */
    public function activatorSettingsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ACTIVATOR')) {
            throw $this->createAccessDeniedException('You are not allowed to edit activator settings');
        }

        /** @var User $user */
        $user = $this->getUser();
        $memberOrganization = $user->getMemberOrganization();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ActivatorSettingsType::class, $memberOrganization, [
            'action' => $this->generateUrl('cocorico_admin__activator_settings'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($memberOrganization);
            $em->flush();

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('flash_action_activator_settings_success', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('cocorico_admin__activator_settings');
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/activator_settings.html.twig', [
                'memberOrganization' => $memberOrganization,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("facilitator/settings", name="cocorico_admin__facilitator_settings")
     * @Method({"GET", "POST"})
     */
    public function facilitatorSettingsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_FACILITATOR')) {
            throw $this->createAccessDeniedException('You are not allowed to edit facilitator settings');
        }

        /** @var User $user */
        $user = $this->getUser();
        $memberOrganization = $user->getMemberOrganization();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(FacilitatorSettingsType::class, $memberOrganization, [
            'action' => $this->generateUrl('cocorico_admin__facilitator_settings'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($memberOrganization);
            $em->flush();

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('flash_action_facilitator_settings_success', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('cocorico_admin__facilitator_settings');
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/facilitator_settings.html.twig', [
                'memberOrganization' => $memberOrganization,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("preferences", name="cocorico_admin__preferences")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function adminPreferencesAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(AdminPreferencesType::class, $user, [
            'action' => $this->generateUrl('cocorico_admin__preferences'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('flash_action_preferences_success', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('cocorico_admin__preferences');
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/preferences.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/listing/validate/{id}", name="cocorico_admin__listing_validate")
     * @Method("GET")
     */
    public function listingValidateAction(Request $request, Listing $listing): RedirectResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') || !$this->isGranted('EDIT', $listing)) {
            throw $this->createAccessDeniedException('Cant validate this listing');
        }

        $em = $this->getDoctrine()->getManager();
        if ($listing->getStatus() === Listing::STATUS_TO_VALIDATE) {
            try {
                $listing->setStatus(Listing::STATUS_PUBLISHED);
                $em->persist($listing);
                $this->addFlash(
                    'sonata_flash_success',
                    $this->get('translator')->trans(
                        'flash_action_listing_validate_success',
                        [],
                        'SonataAdminBundle')
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->get('translator')->trans(
                        'flash_action_listing_validate_error',
                        [],
                        'SonataAdminBundle')
                );
            }
        }
        $em->flush();

        return new RedirectResponse(
            $this->generateUrl('listing-validation_list')
        );
    }

    /**
     * @Route("message/validation/detail/{id}", name="cocorico_admin__message_validate_detail")
     * @Method("GET")
     *
     * @param Thread $thread
     * @return Response|null
     */
    public function messageValidationDetailAction(Thread $thread): ?Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')
            && !$this->isGranted('ROLE_FACILITATOR')) {
            throw $this->createAccessDeniedException('You are not allowed to display this message thread');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $currentMoId = $currentUser->getMemberOrganization()->getId();
        $isSuperAdmin = $this->isGranted('ROLE_SUPER_ADMIN');

        $haveUserFromMO = false;
        $threadMeta = $thread->getAllMetadata();
        foreach ($threadMeta as $meta) {
            if ($meta->getParticipant()->getMemberOrganization()->getId() === $currentMoId) {
                $haveUserFromMO = true;

                break;
            }
        }

        if (!$haveUserFromMO && !$isSuperAdmin) {
            throw $this->createAccessDeniedException('You are not allowed to display this message thread');
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/message_validation_detail.html.twig', [
                'thread' => $thread,
                'isSuperAdmin' => $isSuperAdmin,
                'currentUserMo' => $currentUser->getMemberOrganization(),
            ]
        );
    }

    /**
     * @Route("message/validation/validate/{id}", name="cocorico_admin__message_validate")
     * @Method("GET")
     */
    public function messageValidationValidateAction(Message $message): RedirectResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')
            && !$this->isGranted('ROLE_FACILITATOR')) {
            throw $this->createAccessDeniedException('You are not allowed to validate messages');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $currentMoId = $currentUser->getMemberOrganization()->getId();
        $isSuperAdmin = $this->isGranted('ROLE_SUPER_ADMIN');

        if (!$isSuperAdmin && $currentMoId !== $message->getSender()->getMemberOrganization()->getId()) {
            throw $this->createAccessDeniedException('You are not allowed to validate this message');
        }

        $em = $this->getDoctrine()->getManager();

        $message->setVerified(true);
        $thread = $message->getThread();

        $sender = $message->getSender();
        $recipients = $thread->getOtherParticipants($sender);
        $recipient = (count($recipients) > 0) ? $recipients[0] : $this->getUser();

        $messageEvent = new MessageEvent($message, $message->getThread(), $recipient, $sender);
        $this->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);

        $em->persist($message);
        $em->flush();

        $this->addFlash(
            'sonata_flash_success',
            $this->get('translator')->trans('flash_action_message_validate_success', [], 'SonataAdminBundle')
        );

        return $this->redirectToRoute('cocorico_admin__message_validate_detail', ['id' => $message->getThread()->getId()]);
    }

    /**
     * @Route("message/validation/admin-note/{id}", name="cocorico_admin__message_admin_note")
     * @Method({"GET", "POST"})
     * @param Message $message
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function messageValidationAdminNoteAction(Message $message, Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')
            && !$this->isGranted('ROLE_FACILITATOR')) {
            throw $this->createAccessDeniedException('You are not allowed to add admin notes to messages');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $currentMoId = $currentUser->getMemberOrganization()->getId();
        $isSuperAdmin = $this->isGranted('ROLE_SUPER_ADMIN');

        if (!$isSuperAdmin && $currentMoId !== $message->getSender()->getMemberOrganization()->getId()) {
            throw $this->createAccessDeniedException('You are not allowed to validate this message');
        }

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(MessageAdminNoteType::class, $message, [
            'action' => $this->generateUrl('cocorico_admin__message_admin_note', ['id' => $message->getId()]),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $em->flush();

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('flash_action_message_add_admin_note_success', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('cocorico_admin__message_validate_detail', ['id' => $message->getThread()->getId()]);
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/message_validation_admin_note.html.twig', [
                'message' => $message,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("super-admin/actions", name="cocorico_admin__super_admin_actions")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function superAdminToolsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You are not allowed to visit this page');
        }

        $em = $this->getDoctrine()->getManager();

        $roleFilterForm = $this->createForm(SuperAdminMailType::class, null, [
            'action' => $this->generateUrl('cocorico_admin__super_admin_actions'),
        ])->handleRequest($request);

        if ($roleFilterForm->isSubmitted() && $roleFilterForm->isValid()) {
            $data = $roleFilterForm->getData();

            /** @var UserRepository $userRepository */
            $userRepository = $em->getRepository(User::class);

            $users = [];
            if (!empty($data['roles'])) {
                $users = $userRepository->findByRoles(...$data['roles']);
            }
            $users = array_map(function (User $user) {
                return $user->getEmail();
            }, $users);

            $emails = implode(',', $users);
            $mailToLink = 'mailto:?bcc=' . $emails;
            $userCount = count($users);
        }

        $testMailForm = $this->createForm(TestMailType::class, null, [
            'action' => $this->generateUrl('cocorico_admin__super_admin_actions'),
        ])->handleRequest($request);

        if ($testMailForm->isSubmitted() && $testMailForm->isValid()) {
            /** @var TwigSwiftMailer $mailer */
            $mailer = $this->get('cocorico_user.mailer.twig_swift');

            $data = $testMailForm->getData();
            $email = $data['email'];

            $mailer->sendTest($email);

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('flash_action_message_test_email_sent_success', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('cocorico_admin__super_admin_actions');
        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/super_admin_action.html.twig', [
                'userFilterForm' => $roleFilterForm->createView(),
                'testMailForm' => $testMailForm->createView(),
                'mailToLink' => $mailToLink ?? '',
                'emails' => $emails ?? '',
                'userCount' => $userCount ?? 0,
            ]
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     *
     * @Route("super-admin/cache-clear", name="cocorico_admin__super_admin__cache_clear")
     * @Method({"GET"})
     */
    public function cacheClearAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You are not allowed to visit this page');
        }

        $php = $this->getParameter('cocorico_config_php_cli_path');

        //Clear cache
        $command = $php . ' ../bin/console cache:clear --env=prod';

        $process = new Process($command);

        try {
            $process->mustRun();
            $content = $process->getOutput();
            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('super_admin.cache_clear.success', [], 'SonataAdminBundle')
            );
        } catch (ProcessFailedException $e) {
            $content = $e->getMessage();
            $this->addFlash(
                'sonata_flash_error',
                $this->get('translator')->trans('super_admin.cache_clear.error', [], 'SonataAdminBundle')
            );
        }

        $referer = $request->headers->get('referer');
        if ($referer !== '') {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('cocorico_admin__super_admin_actions');
    }

    /**
     * @Route("invitation/resend/{id}", name="cocorico_admin__resend_invitation")
     * @Method({"GET"})
     * @param UserInvitation $invitation
     * @return RedirectResponse
     */
    public function resendInvitation(UserInvitation $invitation): RedirectResponse
    {
        if (!$invitation->isExpired()) {
            $this->addFlash(
                'sonata_flash_error',
                $this->get('translator')->trans('invitation.resend.is_not_expired.error', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('invitations_list');
        }

        if ($invitation->isUsed()) {
            $this->addFlash(
                'sonata_flash_error',
                $this->get('translator')->trans('invitation.resend.already_used.error', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('invitations_list');
        }

        /** @var TwigSwiftMailer $mailer */
        $mailer = $this->get('cocorico_user.mailer.twig_swift');
        $mailer->sendUserInvited($invitation->getEmail());

        $em = $this->getDoctrine()->getManager();
        $invitation->setExpiration((new \DateTime())->add(new DateInterval('P7D')));
        $em->persist($invitation);
        $em->flush();

        $this->addFlash(
            'sonata_flash_success',
            $this->get('translator')->trans('invitation.resend.success', [], 'SonataAdminBundle')
        );

        return $this->redirectToRoute('invitations_list');
    }

    /**
     * @param Contact $contact
     * @return FormInterface
     */
    private function createContactReplyForm(Contact $contact): FormInterface
    {
        return $this->createForm(ContactReplyType::class, $contact, [
            'action' => $this->generateUrl('cocorico_admin__contact_resolve', ['id' => $contact->getId()]),
        ]);
    }

    /**
     * @param int $contactId
     * @return Response|null
     */
    public function renderContactReplyFormAction(int $contactId): ?Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var ContactRepository $contactRepository */
        $contactRepository = $em->getRepository(Contact::class);

        $contact = $contactRepository->find($contactId);

        $form = $this->createContactReplyForm($contact);

        return $this->render('CocoricoSonataAdminBundle::CustomActions/_contact_reply.html.twig', [
                'contact' => $contact,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("contact/resolve/{id}", name="cocorico_admin__contact_resolve")
     * @Method({"POST"})
     * @param Contact $contact
     * @param Request $request
     * @return RedirectResponse
     */
    public function contactResolveAction(Contact $contact, Request $request): RedirectResponse
    {
        if (!$this->isGranted(BaseVoter::EDIT, $contact)) {
            throw $this->createAccessDeniedException('You are not allowed to visit this page');
        }

        $em = $this->getDoctrine()->getManager();

        $form = $this->createContactReplyForm($contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($contact->getStatus() === BaseContact::STATUS_READ) {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->get('translator')->trans('admin.contact.resolve.already_resolved.error', [], 'SonataAdminBundle')
                );

                return $this->redirectToRoute('admin_cocorico_contact_contact_list');
            }

            $contact->setStatus(BaseContact::STATUS_READ);

            /** @var \Cocorico\ContactBundle\Mailer\TwigSwiftMailer $mailer */
            $mailer = $this->get('cocorico_contact.mailer.twig_swift');

            if (!$contact->isReplySend() && !empty($contact->getReply())) {
                $mailer->sendReply($contact);
                $contact->setReplySend(true);
            }

            $em->persist($contact);
            $em->flush();

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans('admin.contact.resolve.success', [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('admin_cocorico_contact_contact_list');
        }

        return new RedirectResponse($request->headers->get('referer'));
    }
}
