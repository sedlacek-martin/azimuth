<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Repository;

use Cocorico\ContactBundle\Model\BaseContact;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class ContactRepository extends EntityRepository
{

    /**
     * @param string $role
     * @param int|null $moId
     * @return QueryBuilder
     */
    public function getCountNewByRoleQb(string $role, ?int $moId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('COUNT(c.id) as cnt')
            ->leftJoin('c.user', 'user')
            ->leftJoin('user.memberOrganization', 'mo');

        if ($moId) {
            $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->andX("c.user IS NOT NULL", 'mo.id = :moId'),
                    $qb->expr()->isNull("c.user")
                ))
                ->setParameter('moId', $moId);
        }
           $qb
            ->andWhere('c.recipientRoles LIKE :role')
            ->andWhere('c.status = :statusNew')
            ->setParameter('role', "%{$role}%")
            ->setParameter('statusNew', BaseContact::STATUS_NEW);

        return $qb;
    }

    /**
     * @param string $role
     * @param int|null $moId
     * @param \DateTime $from
     * @param \DateTime $to
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountNewByRoleByDates(string $role, ?int $moId, \DateTime $from, \DateTime $to)
    {
        $qb = $this->getCountNewByRoleQb($role, $moId)
            ->andWhere('c.createdAt >= :from')
            ->andWhere('c.createdAt <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        $result = $qb->getQuery()->getSingleResult();

        return $result['cnt'];
    }

    /**
     * @param string $role
     * @param int|null $moId
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountNewByRole(string $role, ?int $moId = null): int
    {
        $result = $this->getCountNewByRoleQb($role, $moId)->getQuery()->getSingleResult();

        return $result['cnt'];
    }
}
