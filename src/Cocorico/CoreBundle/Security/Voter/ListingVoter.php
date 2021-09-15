<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\BaseListing;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ListingVoter extends Voter
{
    const BOOKING = 'booking';
    const EDIT = 'edit';
    const VIEW = 'view';

    const ATTRIBUTES = [
        self::BOOKING,
        self::VIEW,
        self::EDIT,
    ];
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return boolean
     */
    public function supports($attribute, $subject)
    {
        if (!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof Listing) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return integer
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $method = 'voteOn' . str_replace('_', '', ucwords($attribute, '_'));
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('Expected method ' . $method . ' was not found.');
        }

        return $this->{$method}($subject, $token);
    }

    /**
     * @param Listing $listing
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnBooking(Listing $listing, TokenInterface $token)
    {
        return (
            $listing->getStatus() == Listing::STATUS_PUBLISHED
            || (
                $token->getUser() instanceof UserInterface &&
                $token->getUser()->getId() === $listing->getUser()->getId() &&
                $listing->getStatus() != Listing::STATUS_DELETED
            )
        );
    }

    /**
     * @param Listing $listing
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEdit(Listing $listing, TokenInterface $token)
    {
        return (
            $token->getUser() instanceof UserInterface &&
            $token->getUser()->getId() === $listing->getUser()->getId()
        );
    }

    /**
     * @param Listing $listing
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnView(Listing $listing, TokenInterface $token)
    {
        return (
            $listing->getStatus() == BaseListing::STATUS_PUBLISHED
            || (
                $token->getUser() instanceof UserInterface &&
                $token->getUser()->getId() === $listing->getUser()->getId() &&
                $listing->getStatus() != BaseListing::STATUS_DELETED
            ) || (
                $this->authorizationChecker->isGranted('ROLE_ADMIN')
            )
        );
    }
}
