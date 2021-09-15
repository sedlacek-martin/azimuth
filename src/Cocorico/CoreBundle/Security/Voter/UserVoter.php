<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserVoter extends BaseVoter
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {

        $this->authorizationChecker = $authorizationChecker;
    }

    function getAttributes(): array
    {
        return [self::EDIT];
    }

    function getClass(): string
    {
        return User::class;
    }

    public function voteOnEdit(User $user, TokenInterface $token): bool
    {
        if ($this->authorizationChecker->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }

        /** @var User $user */
        $loggedInUser = $token->getUser();
        return $user->getMemberOrganization()->getId() === $loggedInUser->getMemberOrganization()->getId();
    }
}