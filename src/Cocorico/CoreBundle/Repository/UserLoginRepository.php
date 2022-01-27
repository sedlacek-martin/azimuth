<?php

namespace Cocorico\CoreBundle\Repository;

use Doctrine\ORM\NoResultException;

class UserLoginRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int
     * @throws NoResultException
     */
    public function countAll(\DateTime $from = null, \DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('ul')
            ->select('COUNT(ul.id) as cnt');

        if ($from) {
            $qb->andWhere('ul.createdAt > :from')
                ->setParameter('from', $from->format('Y-m-d H:i:s'));
        }

        if ($to) {
            $qb->andWhere('ul.createdAt < :to')
                ->setParameter('to', $to->format('Y-m-d H:i:s'));
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['cnt'];
    }
}
