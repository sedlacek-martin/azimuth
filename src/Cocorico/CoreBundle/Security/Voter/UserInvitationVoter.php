<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\CoreBundle\Entity\UserInvitation;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserInvitationVoter extends BaseVoter
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param UserInvitation $userInvitation
     * @param TokenInterface $token
     * @return bool
     */
    public function voteOnEdit(UserInvitation $userInvitation, TokenInterface $token): bool
    {
        if ($this->authorizationChecker->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        if ($userInvitation->getMemberOrganization() === null) {
            return true;
        }

        return $userInvitation->getMemberOrganization()->getId() === $user->getMemberOrganization()->getId();
    }

    public function voteOnDelete(UserInvitation $userInvitation, TokenInterface $token): bool
    {
        return $this->voteOnEdit($userInvitation, $token);
    }

    function getAttributes(): array
    {
        return [
            self::EDIT,
            self::DELETE,
        ];
    }

    function getClass(): string
    {
        return UserInvitation::class;
    }
}