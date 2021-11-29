<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\ContactBundle\Entity\Contact;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContactVoter extends BaseVoter
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
     * @param Contact $contact
     * @param TokenInterface $token
     * @return bool
     */
    public function voteOnEdit(Contact $contact, TokenInterface $token): bool
    {
        if ($this->authorizationChecker->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        foreach ($user->getRoles() as $role) {
            if (in_array($role, $contact->getRecipientRoles())) {

                return true;
            }
        }

        return false;
    }

    public function voteOnDelete(Contact $contact, TokenInterface $token): bool
    {
        return $this->voteOnEdit($contact, $token);
    }

    public function voteOnView(Contact $contact, TokenInterface $token): bool
    {
        return  $this->voteOnEdit($contact, $token);
    }

    function getAttributes(): array
    {
        return [
            self::EDIT,
            self::DELETE,
            self::VIEW,
        ];
    }

    function getClass(): string
    {
        return Contact::class;
    }
}