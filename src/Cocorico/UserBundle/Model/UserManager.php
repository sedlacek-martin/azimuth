<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Model;

use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Entity\UserImage;
use Cocorico\UserBundle\Entity\UserTranslation;
use Cocorico\UserBundle\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class UserManager extends BaseUserManager implements UserManagerInterface
{
    protected $objectManager;

    protected $repository;

    protected $kernelRoot;

    protected $dispatcher;

    protected $timeUnitIsDay;

    protected $timeZone;

    /** @var TwigSwiftMailer */
    private $mailer;

    /**
     * Constructor.
     *
     *
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
     * @param ObjectManager            $objectManager
     * @param string                   $class
     * @param String                   $kernelRoot
     * @param EventDispatcherInterface $dispatcher
     * @param int                      $timeUnit
     * @param string                   $timeZone
     */
    public function __construct(
        PasswordUpdaterInterface $passwordUpdater,
        CanonicalFieldsUpdater $canonicalFieldsUpdater,
        ObjectManager $objectManager,
        $class,
        $kernelRoot,
        EventDispatcherInterface $dispatcher,
        TwigSwiftMailer $mailer,
        $timeUnit,
        $timeZone
    ) {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $objectManager, $class);

        $this->objectManager = $objectManager;
        $this->repository = $objectManager->getRepository($class);

        $this->kernelRoot = $kernelRoot;
        $this->dispatcher = $dispatcher;
        $this->mailer = $mailer;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->timeZone = $timeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        $user = parent::createUser();
        //Set user timezone to default app timezone
        if (!$this->timeUnitIsDay) {
            $user->setTimeZone($this->timeZone);
        }

        return $user;
    }

    /**
     * Updates a user.
     *
     * @param UserInterface|User $user
     * @param Boolean            $andFlush Whether to flush the changes (default true)
     *
     * @return User|UserInterface
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        /* @var User $user */
        $user->mergeNewTranslations();
        $user->generateSlug();

        $this->persistAndFlush($user);

        /** @var UserTranslation $translation */
        foreach ($user->getTranslations() as $translation) {
            $this->objectManager->persist($translation);
        }
        $this->objectManager->flush();
        $this->objectManager->refresh($user);

        return $user;
    }

    /**
     * @param  User $user
     * @param       $images
     * @param bool $persist
     * @return User
     * @throws AccessDeniedException
     */
    public function addImages(User $user, $images, $persist = false)
    {
        //@todo : see why user is anonymous and not authenticated
        if ($user) {
            $nbImages = $user->getImages()->count();

            foreach ($images as $i => $image) {
                $userImage = new UserImage();
                $userImage->setName($image);
                $userImage->setPosition($nbImages + $i + 1);
                $user->addImage($userImage);
            }

            if ($persist) {
                $this->objectManager->persist($user);
                $this->objectManager->flush();
            }
        } else {
            throw new AccessDeniedException();
        }

        return $user;
    }

    /**
     * @param  User    $user
     * @param  string  $imageName
     * @param  string  $existingPicture
     * @param  boolean $persist
     * @throws AccessDeniedException
     */
    public function addImagesSetFirst(User $user, $imageName, $existingPicture, $persist = false)
    {
        if ($user) {
            $pos = 2;
            foreach ($user->getImages() as $image) {
                if ($existingPicture == $image->getName()) {
                    $user->removeImage($image);
                    $this->objectManager->remove($image);
                } else {
                    $image->setPosition($pos);
                    $this->objectManager->persist($image);
                    $pos++;
                }
            }

            $userImage = new UserImage();
            $userImage->setName($imageName);
            $userImage->setPosition(1);
            $user->addImage($userImage);

            if ($persist) {
                $this->objectManager->persist($user);
                $this->objectManager->flush();
            }
        } else {
            throw new AccessDeniedException();
        }
    }

    public function sendUserExpired(User $user)
    {
        if (!$user->isExpiredSend()) {
            $this->mailer->sendUserExpired($user);

            $user->setExpiredSend(true);
            $this->persistAndFlush($user);

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function sendExpireNotifications(): int
    {
        $count = 0;
        $users = $this->getRepository()->findAllToNotifyExpire(30);

        foreach ($users as $user) {
            $count += (int) $this->notifyExpire($user);
        }

        return $count;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function notifyExpire(User $user): bool
    {
        if (!$user->isExpiredSend()) {
            $this->mailer->sendUserExpired($user);

            $user->setExpiredSend(true);
            $this->persistAndFlush($user);

            return true;
        }

        return false;
    }

    public function sendUserDeleted(User $user): bool
    {
        if ($user->isToBeDeleted()) {
            $this->mailer->sendUserDeleted($user);

            return true;
        }

        return false;
    }

    /**
     * @return UserRepository
     */
    public function getRepository()
    {
        return $this->objectManager->getRepository('CocoricoUserBundle:User');
    }

    public function persistAndFlush($entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
