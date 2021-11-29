<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Repository;

use Cocorico\UserBundle\Entity\User;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    /**
     * Get active user
     *
     * @param integer $idUser
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActiveUser($idUser)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where('u.id = :idUser')
            ->setParameter('idUser', $idUser)
            ->andWhere('u.enabled = :enabled')
            ->setParameter('enabled', 1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $email
     *
     * @return mixed|null
     */
    public function findOneByEmail($email)
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->addSelect("u")
            ->where('u.email = :email')
            ->setParameter('email', $email);
        try {
            $query = $queryBuilder->getQuery();

            return $query->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Get  user by id
     *
     * @param integer $idUser
     *
     * @return mixed
     */
    public function getFindOneQueryBuilder($idUser)
    {
        $queryBuilder =
            $this->createQueryBuilder('u')
                ->addSelect('ut')
                ->leftJoin('u.translations', 'ut')
                ->where('u.id = :idUser')
                ->setParameter('idUser', $idUser);

        return $queryBuilder;
    }

    /**
     * @return array|null
     */
    public function findAllEnabled()
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.enabled = :enabled')
            ->andWhere('u.roles NOT LIKE :roles')
            ->setParameter('enabled', true)
            ->setParameter('roles', '%ROLE_SUPER_ADMIN%');

        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param string $hash
     * @return User|null
     */
    public function findOneByUniqueHash(string $hash): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->addSelect('u')
            ->where('u.uniqueHash = :hash')
            ->setParameter('hash', $hash);
        try {
            $query = $queryBuilder->getQuery();

            return $query->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param int $daysBefore
     * @return ArrayCollection
     */
    public function findAllToNotifyExpire(int $daysBefore = 30): ArrayCollection
    {
        $qb = $this->createQueryBuilder('u')
            ->addSelect('u')
            ->andWhere('u.expiryDate <= :notifyDate')
            ->andWhere('u.expiryNotificationSend = 0');

        $notifyDate = (new \DateTime('now'))->add(new DateInterval('P' . ($daysBefore) . 'D'));

        $qb->setParameter(':notifyDate', $notifyDate->format('Y-m-d H:i:s'));

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * @return ArrayCollection<User>
     */
    public function findAllExpired(): ArrayCollection
    {
        $qb = $this->createQueryBuilder('u')
            ->addSelect('u')
            ->andWhere('u.expiryDate < :today');

        $qb->setParameter(':today', (new \DateTime())->format('Y-m-d H:i:s'));

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * @param int|null $moId
     * @return int
     */
    public function getWaitingActivationCount(int $moId = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as cnt')
            ->where('u.trusted = 0');

        if ($moId) {
            $qb
                ->leftJoin('u.memberOrganization', 'mo')
                ->andWhere('mo.id = :moId')
                ->setParameter('moId', $moId);
        }
        $result = $qb->getQuery()->getSingleResult();
        return $result['cnt'];
    }

    /**
     * @param string ...$roles
     * @return User[]
     */
    public function findByRoles(string... $roles): array
    {
        $qb = $this->createQueryBuilder('u');

        foreach ($roles as $index => $role) {
            $parameterName = 'role_' . $index;
            $qb->orWhere("u.roles LIKE :{$parameterName}")
                ->setParameter($parameterName, "%{$role}%");
        }

        return $qb->getQuery()->getResult();
    }

    public function findByRoleAndMo(string $role, string $memberOrganizationId)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->andWhere("u.roles LIKE :role")
            ->andWhere("u.memberOrganization = :moId")
            ->setParameter('role', "%{$role}%")
            ->setParameter('moId', $memberOrganizationId);


        return $qb->getQuery()->getResult();

    }

    /**
     * @return int
     * @throws NoResultException
     */
    public function getTotalCount(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as cnt');

        $result = $qb->getQuery()->getSingleResult();
        return $result['cnt'];
    }

    /**
     * @return int
     * @throws NoResultException
     */
    public function getActivatedCount(): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as cnt')
            ->andWhere('c.enabled = 1 AND c.trusted = 1');

        $result = $qb->getQuery()->getSingleResult();
        return $result['cnt'];
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getAverageActivationTime(\DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->addSelect('SUM(TIME_DIFF(u.verifiedAt, u.createdAt)) as total')
            ->addSelect('COUNT(u.id) as cnt')
            ->andWhere('u.verifiedAt IS NOT NULL');
        
        if ($from) {
           $qb->andWhere('u.verifiedAt > :from')
               ->setParameter('from', $from->format('Y-m-d H:i:s'));
        }

        if ($to) {
            $qb->andWhere('u.verifiedAt < :to')
                ->setParameter('to', $to->format('Y-m-d H:i:s'));
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getWaitingActivationCountByMo(\DateTime $from, \DateTime $to): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.memberOrganization', 'mo')
            ->select('COUNT(u.id) as cnt, mo.id as mo_id')
            ->where('u.trusted = 0')
            ->andWhere('u.createdAt >= :from')
            ->andWhere('u.createdAt <= :to')
            ->groupBy('mo.id')
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function getReconfirmCountByMo(\DateTime $from, \DateTime $to): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.memberOrganization', 'mo')
            ->select('COUNT(u.id) as cnt, mo.id as mo_id')
            ->where('u.reconfirmRequested = 1')
            ->andWhere('u.reconfirmRequestedAt >= :from')
            ->andWhere('u.reconfirmRequestedAt <= :to')
            ->groupBy('mo.id')
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        $result = $qb->getQuery()->getResult();

        return $result;

    }
}
