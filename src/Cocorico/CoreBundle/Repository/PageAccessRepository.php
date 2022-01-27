<?php

namespace Cocorico\CoreBundle\Repository;

use Doctrine\ORM\NoResultException;

class PageAccessRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int
     * @throws NoResultException
     */
    public function countAll(string $slug, \DateTime $from = null, \DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('pa')
            ->select('COUNT(pa.id) as cnt')
            ->andWhere('pa.slug = :slug')
            ->setParameter('slug', $slug);

        if ($from) {
            $qb->andWhere('pa.accessedAt > :from')
                ->setParameter('from', $from->format('Y-m-d H:i:s'));
        }

        if ($to) {
            $qb->andWhere('pa.accessedAt < :to')
                ->setParameter('to', $to->format('Y-m-d H:i:s'));
        }

        $result = $qb->getQuery()->getSingleResult();

        return $result['cnt'];
    }
}
