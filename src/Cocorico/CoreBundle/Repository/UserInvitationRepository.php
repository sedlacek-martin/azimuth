<?php

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\UserInvitation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class UserInvitationRepository extends EntityRepository
{
    /**
     * @param string $email
     * @return UserInvitation|null
     */
    public function findOneByEmail(string $email): ?UserInvitation
    {
        try {
            return $this->createQueryBuilder('ui')
                ->andWhere('ui.email = :email')
                ->andWhere('ui.used = 0')
                ->andWhere('ui.expiration >= :now')
                ->setParameter('email', $email)
                ->setParameter('now', new \DateTime())
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }


}