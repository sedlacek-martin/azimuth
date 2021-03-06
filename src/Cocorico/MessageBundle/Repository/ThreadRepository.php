<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Repository;

use Cocorico\MessageBundle\Entity\Thread;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * ThreadRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ThreadRepository extends EntityRepository
{
    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int
     * @throws NoResultException
     */
    public function countAll(\DateTime $from = null, \DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('th')
            ->select('COUNT(th.id) as cnt');

        if ($from) {
            $qb->andWhere('th.createdAt > :from')
                ->setParameter('from', $from->format('Y-m-d H:i:s'));
        }

        if ($to) {
            $qb->andWhere('th.createdAt < :to')
                ->setParameter('to', $to->format('Y-m-d H:i:s'));
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['cnt'];
    }

    /**
     * @param int $id1
     * @param int $id2
     * @return Thread|null
     * @throws NonUniqueResultException
     */
    public function findOneByUsers(int $id1, int $id2)
    {
        $qb = $this->createQueryBuilder('th')
            ->andWhere('(th.createdBy = :id1 AND th.user = :id2) OR (th.createdBy = :id2 AND th.user = :id1)')
            ->setParameter('id1', $id1)
            ->setParameter('id2', $id2);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}
