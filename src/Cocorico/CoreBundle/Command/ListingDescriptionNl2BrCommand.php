<?php

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListingDescriptionNl2BrCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cocorico:listing:nl2br')
            ->setDescription('Converts newlines to br for listing descriptions');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManagerInterface em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var ListingRepository $listingRepository */
        $listingRepository = $em->getRepository(Listing::class);
        $listings = $listingRepository->findAll();

        $i = 0;

        foreach ($listings as $listing) {
            $translations = $listing->getTranslations();
            foreach ($translations as $translation) {
                $description = $translation->getDescription();
                $translation->setDescription(nl2br($description));
                $em->persist($translation);
                if (++$i % 50 === 0) {
                    $em->flush();
                }
            }
        }

        $em->flush();

        $output->writeln('Finish');

        return 1;
    }
}
