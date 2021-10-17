<?php

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\ListingListingCharacteristic;
use Doctrine\ORM\EntityRepository;

class ListingListingCharacteristicRepository extends EntityRepository
{
    public function deleteByCharacteristicAndCategory(int $characteristicId, int ...$categoryIds)
    {
        if (empty($categoryIds)) {
            return;
        }

        $idsToDelete = $this->createQueryBuilder('llc')
            ->select('llc.id')
            ->leftJoin('llc.listing', 'l')
            ->leftJoin('l.category', 'c')
            ->leftJoin('llc.listingCharacteristic', 'lc')
            ->andWhere('c.id NOT IN (:categories)')
            ->andWhere('lc.id = :characteristic')
            ->setParameter('categories', $categoryIds)
            ->setParameter('characteristic', $characteristicId)
            ->getQuery()
            ->getResult();

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->delete(ListingListingCharacteristic::class, 'llc')
            ->where('llc.id IN (:ids)')
            ->setParameter('ids', $idsToDelete)
            ->getQuery()
            ->execute();


    }

}