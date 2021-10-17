<?php

namespace Cocorico\UserBundle\Admin;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class BaseUserAdmin extends \Sonata\UserBundle\Admin\Model\UserAdmin
{
    /**
     * @return ContainerInterface|null
     */
    private function getContainer(): ?ContainerInterface
    {
        return $this->getConfigurationPool()->getContainer();
    }

    /**
     * @return TokenStorageInterface|null
     */
    private function getTokenStorage(): ?TokenStorageInterface
    {
        return $this->getContainer()->get('security.token_storage');
    }


    /**
     * @return AuthorizationCheckerInterface|null
     */
    public function getAuthorizationChecker(): ?AuthorizationCheckerInterface
    {
        return $this->getContainer()->get('security.authorization_checker');
    }

    /**
     * @param $attributes
     * @param null $subject
     * @return bool
     */
    public function authIsGranted($attributes, $subject = null): bool
    {
        try {
            return $this->getAuthorizationChecker()->isGranted($attributes, $subject);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        $token = $this->getTokenStorage()->getToken();
        /** @var User $currentUser */
        $user = $token ? $token->getUser() : null;

        if ($user instanceof User) {
            return $user;
        }
        return null;
    }
}