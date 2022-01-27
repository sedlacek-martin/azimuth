<?php

namespace Cocorico\CoreBundle\DataFixtures\ORM;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MemberOrganizationFixture extends Fixture implements ContainerAwareInterface
{
    /** @var  ContainerInterface container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $mo = new MemberOrganization();
        $mo->setDescription('test mo');
        $mo->setName('TEST member organization');
        $mo->setCountry('CZ');

        $manager->persist($mo);
        $manager->flush();

        $this->addReference('test-mo', $mo);
    }
}
