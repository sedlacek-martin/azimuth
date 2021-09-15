<?php

namespace Cocorico\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Repository\MemberOrganizationRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class MemberOrganizationManager extends BaseManager
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var TokenStorage
     */
    private $securityTokenStorage;

    public function __construct(EntityManager $em, TokenStorage $securityTokenStorage)
    {
        $this->em = $em;
        $this->securityTokenStorage = $securityTokenStorage;
    }

    /**
     *
     * @return MemberOrganizationRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoCoreBundle:MemberOrganization');
    }


}