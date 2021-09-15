<?php

namespace Cocorico\CoreBundle\Controller\Admin;

use Cocorico\CoreBundle\Entity\VerifiedDomain;
use Cocorico\UserBundle\Entity\User;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class VerifiedDomainController extends CRUDController
{
    /**
     * @param Request $request
     * @param VerifiedDomain $object
     * @return Response|null
     */
    protected function preEdit(Request $request, $object)
    {

        // TODO: This is not working -> this method is not called

        /** @var User $user */
        $user = $request->getUser();
        /** @var AuthorizationCheckerInterface $authChecker */
        $authChecker = $this->container->get('security.authorization_checker');
        if (!$authChecker->isGranted('ROLE_SUPER_ADMIN')) {
            if ($user->getMemberOrganization()->getId() !== $object->getMemberOrganization()->getId()) {
                throw $this->createAccessDeniedE('You cant edit this object');
            }
        }
        return null;
    }


}