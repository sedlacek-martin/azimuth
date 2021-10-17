<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseVoter extends Voter
{
    const EDIT = 'EDIT';
    const LIST = 'LIST';
    const CREATE = 'CREATE';
    const DELETE = 'DELETE';
    const VIEW = 'VIEW';
    const EXPORT = 'EXPORT';

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        $className = $this->getClass();
        return ($subject instanceof $className) && in_array(strtoupper($attribute), $this->getAttributes());
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        $method = 'voteOn' . str_replace('_', '', ucwords($attribute, '_'));
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('Expected method ' . $method . ' was not found.');
        }

        return $this->{$method}($subject, $token);
    }

    /**
     * @return string[]
     */
    abstract function getAttributes(): array;

    /**
     * @return string
     */
    abstract function getClass(): string;
}