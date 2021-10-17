<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\CoreBundle\Entity\VerifiedDomain;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class VerifiedDomainVoter extends BaseVoter
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
     * @param VerifiedDomain $verifiedDomain
     * @param TokenInterface $token
     * @return bool
     */
    public function voteOnEdit(VerifiedDomain $verifiedDomain, TokenInterface $token): bool
    {
        if ($this->authorizationChecker->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        return $verifiedDomain->getMemberOrganization()->getId() === $user->getMemberOrganization()->getId();
    }

    public function voteOnDelete(VerifiedDomain $verifiedDomain, TokenInterface $token): bool
    {
        return $this->voteOnEdit($verifiedDomain, $token);
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
        return VerifiedDomain::class;
    }
}