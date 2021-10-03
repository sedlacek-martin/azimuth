<?php

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\CountryInformation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class CountryInformationRepository extends EntityRepository
{

    /**
     * @param string $countryCode
     * @return CountryInformation|null
     */
    public function findByCountryCode(string $countryCode): ?CountryInformation
    {
        $expr = $this->createQueryBuilder('c')->expr();
        $qb = $this->createQueryBuilder('c')
            ->where("c.country = '{$countryCode}'");

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return null;
        }

    }

}