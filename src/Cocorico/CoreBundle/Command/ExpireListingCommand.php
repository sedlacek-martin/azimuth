<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireListingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cocorico:listing:expire')
            ->setDescription('This command will expire users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ListingManager $listingMabooking.nager */
        $listingManager = $this->getContainer()->get('cocorico.listing.manager');

        $expireSoonCount = $listingManager->notifyExpiringListings(30);
        $expiredCount = $listingManager->expireListings();

        $output->writeln("{$expireSoonCount} 'listing expire soon' notification send");
        $output->writeln("{$expiredCount} listing expired");

        return 1;
    }
}
