<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Model\UserManager;
use Cocorico\UserBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cocorico:user:expire')
            ->setDescription('This command will expire users');
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('cocorico_user.user_manager');
        /** @var ListingManager $listingManager */
        $listingManager = $this->getContainer()->get('cocorico.listing.manager');

        $expireNotificationsCount = $userManager->sendExpireNotifications();

        $output->writeln($expireNotificationsCount . " expire notification(s) send");

        $userRepository = $userManager->getRepository();

        /** @var User[] $expiredUsers */
        $expiredUsers = $userRepository->findAllExpired();

        $expiredCount = 0;
        $deletedCount = 0;

        foreach ($expiredUsers as $user) {
            $listingManager->deactivateForUser($user);
            $expiredCount += (int) $userManager->notifyExpire($user);

            if ($user->isToBeDeleted()) {
                $deletedCount += (int) $userManager->sendUserDeleted($user);
                $userManager->deleteUser($user);
            }
        }

        $output->writeln("{$expiredCount} user(s) expired");
        $output->writeln("{$deletedCount} user(s) deleted");

        return 1;
    }


}