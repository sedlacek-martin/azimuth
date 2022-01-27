<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserVoter extends BaseVoter
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getAttributes(): array
    {
        return [self::EDIT, self::DELETE];
    }

    public function getClass(): string
    {
        return User::class;
    }

    public function voteOnEdit(User $user, TokenInterface $token): bool
    {
        return $this->voteOnDelete($user, $token);
    }

    public function voteOnDelete(User $user, TokenInterface $token): bool
    {
        if ($this->authorizationChecker->isGranted('ROLE_DEVELOPER')) {
            return true;
        }

        if ($user->hasRole('ROLE_DEVELOPER')) {
            return false;
        }

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            return false;
        }

        /** @var User $user */
        $actualUser = $token->getUser();

        $sameMo = $user->getMemberOrganization()->getId() === $actualUser->getMemberOrganization()->getId();

        return $sameMo;
    }

    public function voteOnExport(User $user, TokenInterface $token): bool
    {
        return $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');
    }
}
