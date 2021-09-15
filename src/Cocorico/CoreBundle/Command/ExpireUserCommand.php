<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\UserBundle\Model\UserManager;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('cocorico_user.user_manager');
        $result = $userManager->sendExpireNotifications();

        $output->writeln($result . " expire notification(s) send");
    }


}