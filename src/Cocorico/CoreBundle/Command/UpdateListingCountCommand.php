<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\CoreBundle\Model\Manager\MemberOrganizationManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class UpdateListingCountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cocorico:listing:update-count')
            ->setDescription('Updates JSON file containing listing counts by country.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ListingManager $listingManager */
        $listingManager = $this->getContainer()->get('cocorico.listing.manager');

        /** @var MemberOrganizationManager $memberOrganizationManager */
        $memberOrganizationManager = $this->getContainer()->get('cocorico.member_organization.manager');

        $counts = $listingManager->getRepository()->countByCountry();

        $data = ['MAX' => 0];
        foreach ($counts as $count) {
            if ((int) $count['cnt'] > $data['MAX']) {
                $data['MAX'] = (int) $count['cnt'];
            }

            $data[$count['code']] = (int) $count['cnt'];
        }

        /** @var MemberOrganization[] $memberOrganizations */
        $memberOrganizations = $memberOrganizationManager->getRepository()->findAll();
        foreach ($memberOrganizations as $memberOrganization) {
            if (!isset($data[$memberOrganization->getCountry()])) {
                $data[$memberOrganization->getCountry()] = 0;
            }
        }

        $result = json_encode($data);
        //Currencies json file
        $file = $this->getContainer()->getParameter('cocorico.listing_count');
        $fs = $this->getContainer()->get('filesystem');
        try {
            if (!$fs->exists(dirname($file))) {
                $fs->mkdir(dirname($file));
            }

            $fs->dumpFile($file, $result);

            $output->writeln("Listing count updated");

            return true;
        } catch (IOException $e) {
            throw new IOException("An error occurred while creating " . $file);
        }
    }
}