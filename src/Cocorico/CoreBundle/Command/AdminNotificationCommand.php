<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Repository\MessageRepository;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminNotificationCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var TwigSwiftMailer */
    protected $mailer;

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

        $data = $userRepository->getWaitingActivationCountByMo($yesterday, $now);
        $activationsCounts = [];

        foreach ($data as $value) {
            $activationsCounts[$value['mo_id']] = $value['cnt'];
        }

        $activators = $userRepository->findByRoles('ROLE_ACTIVATOR');

        foreach ($activators as $activator) {
            if ($activator->isDisableAdminNotifications()) {
                continue;
            }

            $userMo = $activator->getMemberOrganization()->getId();
            $activationCount = $activationsCounts[$userMo] ?? 0;

            if ($activationCount === 0) {
                continue;
            }

            $this->mailer->sendActivatorNotification($activator, $activationCount);
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
        $postValidationsCounts = [];
        $messageValidationsCounts = [];

        foreach ($dataPosts as $value) {
            $postValidationsCounts[$value['mo_id']] = $value['cnt'];
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

            if ($postValidationCount === 0 && $messageValidationCount === 0) {
                continue;
            }

            $this->mailer->sendFacilitatorNotification($facilitator, $postValidationCount, $messageValidationCount);
        }
    }
}