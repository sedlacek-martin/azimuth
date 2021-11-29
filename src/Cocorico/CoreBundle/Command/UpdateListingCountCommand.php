<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\CoreBundle\Model\Manager\MemberOrganizationManager;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Security\Core\Role\Role;

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
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
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


        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);
        /** @var MemberOrganization[] $memberOrganizations */
        $memberOrganizations = $memberOrganizationManager->getRepository()->findAll();
        foreach ($memberOrganizations as $memberOrganization) {
            if (!isset($data[$memberOrganization->getCountry()])) {

                $facilitators = $userRepository->findByRoleAndMo('ROLE_FACILITATOR', $memberOrganization->getId());
                $activators = $userRepository->findByRoleAndMo('ROLE_ACTIVATOR', $memberOrganization->getId());
                if (count($facilitators) !== 0 && count($activators) !== 0) {
                    $data[$memberOrganization->getCountry()] = 0;
                }
            }
        }

        $result = json_encode($data);

        //Count json file
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