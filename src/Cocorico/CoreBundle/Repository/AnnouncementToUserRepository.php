<?php

namespace Cocorico\CoreBundle\Repository;

use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class AnnouncementToUserRepository extends EntityRepository
{
    public function getAnnouncementsWithCache(User $user)
    {
        $qb = $this->createQueryBuilder('au')
            ->andWhere('au.user = :user')
            ->andWhere('au.dismissed = 0')
            ->andWhere('au.displayed = 0')
            ->setParameter('user', $user);

        $query = $qb->getQuery();
        $query->useResultCache(true, 3600, 'announcements' . $user->getId());

        return $query->getResult();
    }

    public function clearCache(int $userId)
    {
        $resultCache = $this->getEntityManager()->getConfiguration()->getResultCacheImpl();
        $resultCache->delete('announcements' . $userId);

    }

}