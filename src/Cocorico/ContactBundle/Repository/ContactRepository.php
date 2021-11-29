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

class ContactRepository extends EntityRepository
{

    /**
     * @param string $role
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountByRole(string $role): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as cnt')
            ->andWhere('c.recipientRoles LIKE :role')
            ->andWhere('c.status = :statusNew')
            ->setParameter('role', "%{$role}%")
            ->setParameter('statusNew', BaseContact::STATUS_NEW);

        $result = $qb->getQuery()->getSingleResult();

        return $result['cnt'];
    }
}
