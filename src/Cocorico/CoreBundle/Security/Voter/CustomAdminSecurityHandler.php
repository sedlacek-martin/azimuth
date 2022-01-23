<?php

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\UserBundle\Entity\User;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class CustomAdminSecurityHandler extends RoleSecurityHandler
{
    /**
     * {@inheritdoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null): bool
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        $attributesWithRole = [];
        foreach ($attributes as $pos => $attribute) {
            $attributesWithRole[$pos] = sprintf($this->getBaseRole($admin), $attribute);
        }

        $allRole = sprintf($this->getBaseRole($admin), 'ALL');

        // prevent any user form editing developers
        if ($object instanceof User
            && $object->hasRole('ROLE_DEVELOPER')
            && !$this->authorizationChecker->isGranted('ROLE_DEVELOPER')) {
            return false;
        }

        try {
            return $this->authorizationChecker->isGranted($this->superAdminRoles)
                || $this->authorizationChecker->isGranted($attributesWithRole, $object)
                || $this->authorizationChecker->isGranted([$allRole], $object)
                || $this->authorizationChecker->isGranted($attributes, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }
}
