<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\ContactBundle\Entity\Contact;
use Cocorico\ContactBundle\Repository\ContactRepository;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Repository\MessageRepository;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminNotificationCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var TwigSwiftMailer */
    protected $mailer;

    /** @var array */
    protected $contactCountCache = [];

    protected function configure()
    {
        $this
            ->setName('cocorico:admin_notification')
            ->setDescription('Notifies admins about new requests requiring actions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->mailer = $this->getContainer()->get('cocorico.mailer.twig_swift');

        $this->notifyActivators($input, $output);
        $this->notifyFacilitators($input, $output);
        $this->notifySuperAdmins($input, $output);
    }

    protected function notifySuperAdmins(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);

        $now = new \DateTime();
        $yesterday = (new \DateTime())->modify("-1 day");

        $superAdmins = $userRepository->findByRoles('ROLE_SUPER_ADMIN');

        foreach ($superAdmins as $superAdmin) {
            if ($superAdmin->isDisableAdminNotifications()) {
                continue;
            }

            $userMo = $superAdmin->getMemberOrganization()->getId();
            $contactCount = $this->getContactCount('ROLE_SUPER_ADMIN', $userMo, $yesterday, $now);

            if ($contactCount === 0) {
                continue;
            }

            $this->mailer->sendSuperAdminNotification($superAdmin, $contactCount);
        }

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function notifyActivators(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);

        $now = new \DateTime();
        $yesterday = (new \DateTime())->modify("-1 day");

        $activationData = $userRepository->getWaitingActivationCountByMo($yesterday, $now);
        $reconfirmData = $userRepository->getReconfirmCountByMo($yesterday, $now);

        $activationsCounts = [];
        $reconfirmCounts = [];

        foreach ($activationData as $value) {
            $activationsCounts[$value['mo_id']] = $value['cnt'];
        }

        foreach ($reconfirmData as $value) {
            $reconfirmCounts[$value['mo_id']] = $value['cnt'];
        }

        $activators = $userRepository->findByRoles('ROLE_ACTIVATOR');

        foreach ($activators as $activator) {
            if ($activator->isDisableAdminNotifications()) {
                continue;
            }

            $userMo = $activator->getMemberOrganization()->getId();
            $activationCount = $activationsCounts[$userMo] ?? 0;
            $reconfirmCount = $reconfirmCounts[$userMo] ?? 0;
            $contactCount = $this->getContactCount('ROLE_ACTIVATOR', $userMo, $yesterday, $now);

            if ($activationCount === 0 && $reconfirmCount === 0 && $contactCount === 0) {
                continue;
            }

            $this->mailer->sendActivatorNotification($activator, $activationCount, $reconfirmCount, $contactCount);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function notifyFacilitators(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        /** @var ListingRepository $listingRepository */
        $listingRepository = $this->em->getRepository(Listing::class);
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->em->getRepository(Message::class);

        $now = new \DateTime();
        $yesterday = (new \DateTime())->modify("-1 day");

        $dataPosts = $listingRepository->getWaitingForValidationCountByMo($yesterday, $now);
        $dataMessages = $messageRepository->getWaitingForValidationCountByMo($yesterday, $now);
        $dataNewPosts = $listingRepository->getNewCountByMo($yesterday, $now);
        $postValidationsCounts = [];
        $postNewCounts = [];
        $messageValidationsCounts = [];

        foreach ($dataPosts as $value) {
            $postValidationsCounts[$value['mo_id']] = $value['cnt'];
        }

        foreach ($dataNewPosts as $value) {
            $postNewCounts[$value['mo_id']] = $value['cnt'];
        }

        foreach ($dataMessages as $value) {
            $messageValidationsCounts[$value['mo_id']] = $value['cnt'];
        }

        $facilitators = $userRepository->findByRoles('ROLE_FACILITATOR');

        foreach ($facilitators as $facilitator) {
            if ($facilitator->isDisableAdminNotifications()) {
                continue;
            }

            $userMo = $facilitator->getMemberOrganization()->getId();
            $postValidationCount = $postValidationsCounts[$userMo] ?? 0;
            $messageValidationCount = $messageValidationsCounts[$userMo] ?? 0;
            $postNewCount = $postNewCounts[$userMo] ?? 0;
            $contactCount = $this->getContactCount('ROLE_FACILITATOR', $userMo, $yesterday, $now);

            if ($postValidationCount === 0 && $messageValidationCount === 0 && $postNewCount === 0 && $contactCount === 0) {
                continue;
            }

            $this->mailer->sendFacilitatorNotification($facilitator, $postValidationCount, $messageValidationCount, $postNewCount, $contactCount);
        }
    }

    /**
     * @param string $role
     * @param int $moId
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function getContactCount(string $role, int $moId, \DateTime $from, \DateTime $to): int
    {
        /** @var ContactRepository $contactRepository */
        $contactRepository = $this->em->getRepository(Contact::class);

        if (isset($this->contactCountCache[$role][$moId])) {
            return $this->contactCountCache[$role][$moId];
        }

        $this->contactCountCache[$role][$moId] = $contactRepository->getCountByRoleByDates($role, $moId, $from, $to) ?? 0;

        dump($this->contactCountCache);
        return $this->contactCountCache[$role][$moId];
    }
}