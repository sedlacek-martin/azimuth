<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\DataFixtures\ORM;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserFixtures extends Fixture implements ContainerAwareInterface
{
    /** @var  ContainerInterface container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('cocorico_user.user_manager');
        $locale = $this->container->getParameter('cocorico.locale');
        $timeZone = $this->getDefaultUserTimeZone();

        $user = $userManager->createUser();
        $user->setLastName('super-admin');
        $user->setFirstName('super-admin');
        $user->setUsername('super-admin@cocorico.rocks');
        $user->setEmail('super-admin@cocorico.rocks');
        $user->setPlainPassword('super-admin');
        $user->setCountryOfResidence('CZ');
        $user->setBirthday(new DateTime('1978-01-01'));
        $user->setEnabled(true);
        $user->setTrusted(true);
        $user->setTimeZone($timeZone);
        $user->addRole('ROLE_SUPER_ADMIN');

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('super-admin', $user);
    }

    /**
     * Get default user time zone
     * If time unit is day default user timezone = UTC else user timezone = default app time zone
     * @return mixed|string
     */
    private function getDefaultUserTimeZone()
    {
        $userTimeZone = 'UTC';
        $timeUnitIsDay = ($this->container->getParameter('cocorico.time_unit') % 1440 == 0) ? true : false;
        if (!$timeUnitIsDay) {
            $userTimeZone = $this->container->getParameter('cocorico.time_zone');
        }

        return $userTimeZone;
    }
}
