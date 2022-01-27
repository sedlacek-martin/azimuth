<?php

namespace Cocorico\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class MemberOrganizationRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('mo')
            ->select('COUNT(mo.id) as cnt');

        $result = $qb->getQuery()->getSingleResult();

        return $result['cnt'];
    }
}
