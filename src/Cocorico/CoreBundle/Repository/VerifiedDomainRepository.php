<?php

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\VerifiedDomain;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class VerifiedDomainRepository extends EntityRepository
{
    /**
     * @param int $memberOrganizationId
     * @param string $domain
     * @return VerifiedDomain|null
     */
    public function findOneByMoAndDomain(int $memberOrganizationId, string $domain): ?VerifiedDomain
    {
        try {
            return $this->createQueryBuilder('vd')
                ->leftJoin('vd.memberOrganization', 'mo')
                ->andWhere('mo.id = :moId')
                ->andWhere('vd.domain = :domain')
                ->setParameter('moId', $memberOrganizationId)
                ->setParameter('domain', $domain)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
