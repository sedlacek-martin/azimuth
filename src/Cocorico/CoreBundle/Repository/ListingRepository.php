<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\BaseListing;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;

class ListingRepository extends EntityRepository
{
    /**
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindQueryBuilder()
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            //Select
            ->select("partial l.{id, averageRating, certified, createdAt, commentCount}")
            ->addSelect("partial t.{id, locale, slug, title, description}")
//            ->addSelect("partial llcat.{id, listing, category}")
            ->addSelect("partial ca.{id, lft, lvl, rgt, root, pin}")
            ->addSelect("partial pin.{id, name, imagePath}")
            ->addSelect("partial cat.{id, locale, name}")
            ->addSelect("partial i.{id, name}")
            ->addSelect("partial u.{id, firstName}")
            //->addSelect("partial ln.{id}")
            ->addSelect("partial ln.{id, city, route, country}")
            ->addSelect("partial co.{id, lat, lng, latRandom, lngRandom}")
            ->addSelect("partial ui.{id, name}")
            ->addSelect("'' AS DUMMY")//To maintain fields on same array level when extra fields are added

            //From
            ->from('CocoricoCoreBundle:Listing', 'l')
            ->leftJoin('l.translations', 't')
//            ->leftJoin('l.listingListingCategories', 'llcat')
            ->leftJoin('l.category', 'ca')
            ->leftJoin('ca.pin', 'pin')
            //Join::WITH: Avoid exclusion of listings with no categories (disable inner join)
            ->leftJoin('ca.translations', 'cat', Query\Expr\Join::WITH, 'cat.locale = :locale')
            ->leftJoin('l.images', 'i')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.images', 'ui', Query\Expr\Join::WITH, 'ui.position = 1')
            ->leftJoin('l.location', 'ln')
            ->leftJoin('ln.coordinate', 'co');
//            ->leftJoin('co.country', 'cy');

//        $queryBuilder
//            ->addGroupBy('l.id');

        return $queryBuilder;
    }

    /**
     * @param string $slug
     * @param string $locale
     * @param bool   $joined
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindOneBySlugQuery($slug, $locale, $joined = true)
    {
        $slugParts = explode('-', $slug);
        $listingId = end($slugParts);

        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t")
            ->leftJoin('l.translations', 't')
            ->where('l.id = :listingId')
            ->andWhere('t.locale = :locale')
            ->setParameter('listingId', $listingId)
            ->setParameter('locale', $locale);

        if ($joined) {
            $queryBuilder
                ->addSelect("u, i")
                ->leftJoin('l.user', 'u')
                ->leftJoin('u.images', 'i');
        }

        return $queryBuilder;
    }

    /**
     * @param string $slug
     * @param string $locale
     * @param bool   $joined
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlug($slug, $locale, $joined = true)
    {
        try {
            $queryBuilder = $this->getFindOneBySlugQuery($slug, $locale, $joined);

            //$query->useResultCache(true, 3600, 'findOneBySlug');
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param string $slug
     * @param string $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTranslationsBySlug($slug, $locale)
    {
        $listing = $this->findOneBySlug($slug, $locale, false);

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('lt')
            ->from('CocoricoCoreBundle:ListingTranslation', 'lt')
            ->where('lt.translatable = :listing')
            ->setParameter('listing', $listing);
        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param int    $ownerId
     * @param string $locale
     * @param array  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByOwnerQuery($ownerId, $locale, $status)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t, i, c, ca, cat, u")
//            ->addSelect("t, i, c, ca, cat, u, rt")
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'u')
            //->leftJoin('u.reviewsTo', 'rt')
            ->leftJoin('l.listingListingCharacteristics', 'c')
            ->leftJoin('l.images', 'i')
            ->leftJoin('l.category', 'ca')
            ->leftJoin('ca.translations', 'cat')
            ->where('u.id = :ownerId')
            ->andWhere('t.locale = :locale')
            ->andWhere('l.status IN (:status)')
            //->andWhere('rt.reviewTo = :reviewTo')
            ->setParameter('ownerId', $ownerId)
            ->setParameter('locale', $locale)
            ->setParameter('status', $status);

        //->setParameter('reviewTo', $ownerId);

        return $queryBuilder;

    }

    /**
     * @param $ownerId
     * @param $locale
     * @param $status
     * @return array
     */
    public function findByOwner($ownerId, $locale, $status)
    {
        return $this->getFindByOwnerQuery($ownerId, $locale, $status)->getQuery()->getResult();
    }


    /**
     * @param $title
     * @param $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByTitle($title, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t")
            ->addSelect("u, i")
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.images', 'i')
            ->where('t.title = :title')
            ->andWhere('t.locale = :locale')
            ->setParameter('title', $title)
            ->setParameter('locale', $locale);
        try {

            $query = $queryBuilder->getQuery();

            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param bool $withUser
     * @param bool $withTranslations
     *
     * @param int  $hydrationMode
     * @return array|null
     */
    public function findAllPublished(
        $withUser = true,
        $withTranslations = false,
        $hydrationMode = AbstractQuery::HYDRATE_OBJECT
    ) {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.status = :listingStatus')
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);

        if ($withUser) {
            $queryBuilder
                ->addSelect("u")
                ->leftJoin('l.user', 'u');
        }

        if ($withTranslations) {
            $queryBuilder
                ->addSelect("t")
                ->leftJoin('l.translations', 't');
        }

        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult($hydrationMode);
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param $limit
     * @param $locale
     * @param bool $publicOnly
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByHighestRankingQueryBuilder($limit, $locale, bool $publicOnly)
    {
        $queryBuilder = $this->getFindQueryBuilder();

        //Where
        $queryBuilder
            ->where('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED)
            ->setMaxResults($limit)
            ->orderBy('l.createdAt', 'DESC');

        if ($publicOnly) {
            $queryBuilder
                ->andWhere('l.public = 1')
                ->andWhere('l.certified = 1');
        }

        return $queryBuilder;
    }

    /**
     * @param $listingId
     * @param $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindOneByIdAndLocaleQuery($listingId, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("lt")
            ->leftJoin("l.translations", "lt")
            ->where('l.id = :listingId')
            ->andWhere('lt.locale = :locale')
            ->setParameter('listingId', $listingId)
            ->setParameter('locale', $locale);

        return $queryBuilder;
    }

    /**
     * Used by ElasticsearchBundle
     *
     * @param int $listingTranslationId
     * @return array
     */
    public function findByTranslationId($listingTranslationId)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->innerJoin('l.translations', 'lt')
            ->where('lt.id = :listingTranslationId')
            ->setParameter('listingTranslationId', $listingTranslationId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Used by ElasticsearchBundle
     *
     * @param int $listingListingCategoryId
     * @return array
     */
    public function findByListingListingCategoryId($listingListingCategoryId)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->innerJoin('l.listingListingCategories', 'llc')
            ->where('llc.id = :listingListingCategoryId')
            ->setParameter('listingListingCategoryId', $listingListingCategoryId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Used by ElasticsearchBundle
     *
     * @param int $listingCategoryTranslationId
     * @return array
     */
    public function findByListingCategoryTranslationId($listingCategoryTranslationId)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->innerJoin('l.listingListingCategories', 'llc')
            ->innerJoin('llc.category', 'lc')
            ->innerJoin('lc.translations', 'lct')
            ->where('lct.id = :listingCategoryTranslationId')
            ->setParameter('listingCategoryTranslationId', $listingCategoryTranslationId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Used by ElasticsearchBundle
     *
     * @param int $userTranslationId
     * @return array
     */
    public function findByUserTranslationId($userTranslationId)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->innerJoin('l.user', 'lu')
            ->innerJoin('lu.translations', 'lut')
            ->where('lut.id = :userTranslationId')
            ->setParameter('userTranslationId', $userTranslationId);

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByCountry()
    {
        $queryBuilder = $this->createQueryBuilder('listing')
            ->select('COUNT(listing.id) as cnt, country.code as code')
            ->leftJoin('listing.location', 'location')
            ->leftJoin('location.coordinate', 'coordinate')
            ->leftJoin('coordinate.country', 'country')
            ->groupBy('country.code')
            ->getQuery()
            ->getResult();

        return $queryBuilder;
    }

    /**
     * @param int $moId
     * @return int
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWaitingForValidationCount(int $moId = null): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id) as cnt')
            ->where('l.status = :validateStatus')
            ->setParameter('validateStatus', BaseListing::STATUS_TO_VALIDATE);

        if ($moId) {
            $qb
                ->leftJoin('l.user', 'u')
                ->leftJoin('u.memberOrganization', 'mo')
                ->andWhere('mo.id = :moId')
                ->setParameter('moId', $moId);
        }
        $result = $qb->getQuery()->getSingleResult();
        return $result['cnt'];
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getWaitingForValidationCountByMo(\DateTime $from, \DateTime $to): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.memberOrganization', 'mo')
            ->select('COUNT(l.id) as cnt, mo.id as mo_id')
            ->andWhere('l.status = :validateStatus')
            ->andWhere('u.createdAt >= :from')
            ->andWhere('u.createdAt <= :to')
            ->groupBy('mo.id')
            ->setParameter('validateStatus', BaseListing::STATUS_TO_VALIDATE)
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int
     * @throws NoResultException
     */
    public function countAll(\DateTime $from = null, \DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id) as cnt');

        if ($from) {
            $qb->andWhere('l.createdAt > :from')
                ->setParameter('from', $from->format('Y-m-d H:i:s'));
        }

        if ($to) {
            $qb->andWhere('l.createdAt < :to')
                ->setParameter('to', $to->format('Y-m-d H:i:s'));
        }

        $result = $qb->getQuery()->getSingleResult();
        return $result['cnt'];
    }
}


