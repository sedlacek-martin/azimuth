<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Form\Handler;

use Cocorico\CoreBundle\Entity\UserInvitation;
use Cocorico\CoreBundle\Entity\VerifiedDomain;
use Cocorico\CoreBundle\Repository\UserInvitationRepository;
use Cocorico\CoreBundle\Repository\VerifiedDomainRepository;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Mailer\MailerInterface;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Cocorico\UserBundle\Model\UserManager;
use Cocorico\UserBundle\Security\LoginManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class RegistrationFormHandler
{

    const ERROR = 0;

    protected $request;
    /** @var  TwigSwiftMailer */
    protected $mailer;
    /** @var  UserManager */
    protected $userManager;
    protected $formFactory;
    protected $tokenGenerator;
    protected $loginManager;
    protected $dispatcher;
    /** @var ParameterBag */
    private $parameterBag;
    /** @var Session */
    private $session;
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param RequestStack $requestStack
     * @param UserManager $userManager
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param LoginManager $loginManager
     * @param EventDispatcherInterface $dispatcher
     * @param ParameterBag $parameterBag
     * @param Session $session
     */
    public function __construct(
        RequestStack $requestStack,
        UserManager $userManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
        LoginManager $loginManager,
        EventDispatcherInterface $dispatcher,
        ParameterBag $parameterBag,
        Session $session,
        EntityManagerInterface $em
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->loginManager = $loginManager;
        $this->dispatcher = $dispatcher;
        $this->parameterBag = $parameterBag;
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @param Form $form
     * @param bool $confirmation
     * @return bool
     */
    public function process($form, bool $confirmation = false): bool
    {
        /** @var User $user */
        $user = $form->getData();

        if ('POST' === $this->request->getMethod()) {
            $form->handleRequest($this->request);

            $verifiedDomain = null;
            if ($user->getMemberOrganization()->isRegistrationAcceptDomain()) {
                // check whether email is within the trusted domain
                /** @var VerifiedDomainRepository $verifiedDomainRepository */
                $verifiedDomainRepository = $this->em->getRepository(VerifiedDomain::class);
                $verifiedDomain = $verifiedDomainRepository->findOneByMoAndDomain($user->getMemberOrganization()->getId(), $user->getEmailDomain());
            }

            /** @var UserInvitationRepository $userInvitationRepository */
            $userInvitationRepository = $this->em->getRepository(UserInvitation::class);
            $invitation = $userInvitationRepository->findOneByEmail($user->getEmail());

            $hasVerifiedDomain = ($verifiedDomain !== null);
            $hasInvite = ($invitation !== null && !$invitation->isUsed() && !$invitation->isExpired());
            $inviteCountryOk = $invitation && $invitation->getMemberOrganization()->getId() === $user->getMemberOrganization()->getId();

            $inviteOk = $hasInvite && $inviteCountryOk;

            if (!$user->getMemberOrganization()->isRegistrationAcceptActivation() &&
                !$inviteOk && !$hasVerifiedDomain) {
                $form->addError(new FormError('Registration for this member organization is only available via invitation or verified domain'));

                return false;
            }

            if ($form->isValid()) {
                if ($inviteOk && $invitation) {
                    $invitation->setUsed(true);
                    $this->em->persist($invitation);
                    $this->em->flush();
                }
                $this->onSuccess($user, $confirmation, $hasVerifiedDomain, $inviteOk);

                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param bool $confirmation
     * @param bool $verifiedDomain
     * @param bool $invited
     */
    protected function onSuccess(User $user, bool $confirmation, bool $verifiedDomain, bool $invited)
    {
        $this->handleRegistration($user, $confirmation, $verifiedDomain, $invited);
    }

    /**
     * @param User $user
     * @param bool $confirmation
     * @param bool $verifiedDomain
     * @param bool $invited
     */
    public function handleRegistration(User $user, bool $confirmation = false, bool $verifiedDomain = false, bool $invited = false)
    {
        //Set the default mother tongue for registering user
        $user->setMotherTongue($this->request->get('_locale'));
        $daysToAdd = $user->getMemberOrganization()->getUserExpiryPeriod();
        $expiryDate = (new \DateTime())->add(new \DateInterval("P{$daysToAdd}D"));
        $user->setExpiryDate($expiryDate);


        //Eventually change user info before persist it
        $event = new UserEvent($user);
        $this->dispatcher->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        if (!$user->isTrusted()) {

            if ($verifiedDomain || $invited) {
                $user
                    ->setTrusted(true)
                    ->setTrustedEmailSent(true);
            }  else {
                $user->setTrusted(false);
                $this->session->set('cocorico_user_need_verification', true);
            }
        }

        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->userManager->updateUser($user);
            $this->mailer->sendAccountCreationConfirmationMessageToUser($user);
        } else {
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            if ($user->isTrusted()) {
                $this->loginManager->getLoginManager()->loginUser($this->loginManager->getFirewallName(), $user);
                $this->mailer->sendAccountCreatedMessageToUser($user);
            } else {
                $this->mailer->sendWaitingForTrusted($user);
            }
        }
    }

}
