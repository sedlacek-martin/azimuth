<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Event\ListingSearchEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Model\BaseListing;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ListingSearchManager
{
    protected $em;
    protected $dm;
    protected $dispatcher;
    protected $endDayIncluded;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $maxPerPage;
    protected $listingDefaultStatus;

    /**
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        array $parameters
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;

        $parameters = $parameters["parameters"];
        $this->endDayIncluded = $parameters["cocorico_booking_end_day_included"];
        $this->timeUnit = $parameters["cocorico_time_unit"];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;
        $this->maxPerPage = $parameters["cocorico_listing_search_max_per_page"];
        $this->listingDefaultStatus = $parameters["cocorico_listing_availability_status"];
    }

    /**
     * @param ListingSearchRequest $listingSearchRequest
     * @param                      $locale
     *
     * @return Paginator|null
     */
    public function search(ListingSearchRequest $listingSearchRequest, $locale)
    {
        //Select
        $queryBuilder = $this->getRepository()->getFindQueryBuilder();

        //Geo location
        $queryBuilder = $this->getSearchByGeoLocationQueryBuilder($listingSearchRequest, $queryBuilder);

        $queryBuilder
            ->andWhere('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);

        //Dates
        $queryBuilder = $this->getSearchByValidDatesQueryBuilder($listingSearchRequest, $queryBuilder);

        //Categories
        $categories = $listingSearchRequest->getCategories();
        if (count($categories)) {
            $queryBuilder
                ->andWhere("ca.id IN (:categories)")
                ->setParameter("categories", $categories);
        }

        //Characteristics
        $queryBuilder = $this->getSearchByCharacteristicsQueryBuilder($listingSearchRequest, $queryBuilder);

        //Order
        switch ($listingSearchRequest->getSortBy()) {
            case 'distance':
                $queryBuilder->orderBy("distance", "ASC");
                break;
            default:
                $queryBuilder->orderBy("distance", "ASC");
                break;
        }
        $queryBuilder->addOrderBy("l.adminNotation", "DESC");

        if (!$listingSearchRequest->getIsXmlHttpRequest()) {
            $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
            $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH, $event);
            $queryBuilder = $event->getQueryBuilder();
        }

        //Pagination
        if ($listingSearchRequest->getMaxPerPage()) {
            $queryBuilder
                ->setFirstResult(($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage())
                ->setMaxResults($listingSearchRequest->getMaxPerPage());
        }

        //Query
        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);
    }

    public function searchAll($locale, $listingSearchRequest)
    {
        //Select
        $queryBuilder = $this->getRepository()->getFindQueryBuilder();

        $queryBuilder
            ->andWhere('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);

        $queryBuilder->addOrderBy("l.id", "DESC");
        $queryBuilder->addOrderBy("l.adminNotation", "DESC");

        //Pagination
        if ($listingSearchRequest->getMaxPerPage()) {
            $queryBuilder
                ->setFirstResult(($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage())
                ->setMaxResults($listingSearchRequest->getMaxPerPage());
        }

        //Query
        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);

    }

    /**
     * @param ListingSearchRequest $listingSearchRequest
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function getSearchByValidDatesQueryBuilder(ListingSearchRequest $listingSearchRequest, $queryBuilder): QueryBuilder
    {
       $range = $listingSearchRequest->getDateRange();

       if ($range === null) {
           return $queryBuilder;
       }

       if ($range->getStart()) {
           $queryBuilder
               ->andWhere('((l.validFrom <= :from AND l.validTo >= :from) OR l.validFrom IS NULL)')
               ->setParameter('from', $range->getStart());
       }

       if ($range->getEnd()) {
           $queryBuilder
               ->andWhere('((l.validFrom <= :to AND l.validTo >= :to) OR l.validTo IS NULL)')
               ->setParameter('to', $range->getEnd());
       }

        return $queryBuilder;
    }

    /**
     * @param ListingSearchRequest       $listingSearchRequest
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    private function getSearchByGeoLocationQueryBuilder(ListingSearchRequest $listingSearchRequest, $queryBuilder)
    {
        $searchLocation = $listingSearchRequest->getLocation();
        //Select distance
        $queryBuilder
            ->addSelect('GEO_DISTANCE(co.lat = :lat, co.lng = :lng) AS distance')
            ->setParameter('lat', $searchLocation->getLat())
            ->setParameter('lng', $searchLocation->getLng());

        $viewport = $searchLocation->getBound();
        $queryBuilder
            ->where('co.lat < :neLat')
            ->andWhere('co.lat > :swLat')
            ->andWhere('co.lng < :neLng')
            ->andWhere('co.lng > :swLng')
            ->setParameter('neLat', $viewport["ne"]["lat"])
            ->setParameter('swLat', $viewport["sw"]["lat"])
            ->setParameter('neLng', $viewport["ne"]["lng"])
            ->setParameter('swLng', $viewport["sw"]["lng"]);

        return $queryBuilder;
    }

    /**
     * @param ListingSearchRequest       $listingSearchRequest
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    private function getSearchByCharacteristicsQueryBuilder(ListingSearchRequest $listingSearchRequest, $queryBuilder)
    {
        $characteristics = $listingSearchRequest->getCharacteristics();
        $characteristics = array_filter($characteristics);
        if (count($characteristics)) {
            $queryBuilderCharacteristics = $this->em->createQueryBuilder();
            $queryBuilderCharacteristics
                ->select('IDENTITY(c.listing)')
                ->from('CocoricoCoreBundle:ListingListingCharacteristic', 'c');

            foreach ($characteristics as $characteristicId => $characteristicValueId) {
                $queryBuilderCharacteristics
                    ->orWhere(
                        "( c.listingCharacteristic = :characteristic$characteristicId AND c.listingCharacteristicValue = :value$characteristicId )"
                    );

                $queryBuilder
                    ->setParameter("characteristic$characteristicId", $characteristicId)
                    ->setParameter("value$characteristicId", intval($characteristicValueId));
            }

            $queryBuilderCharacteristics
                ->groupBy('c.listing')
                ->having("COUNT(c.listing) = :nbCharacteristics");

            $queryBuilder
                ->setParameter("nbCharacteristics", count($characteristics));

            $queryBuilder
                ->leftJoin('l.listingListingCharacteristics', 'llc')
                ->andWhere(
                    $queryBuilder->expr()->in(
                        'l.id',
                        $queryBuilderCharacteristics->getDQL()
                    )
                );
        }

        return $queryBuilder;
    }

    /**
     * Get listings highest ranked
     *
     * @param ListingSearchRequest $listingSearchRequest
     * @param                      $limit
     * @param                      $locale
     * @param bool $publicOnly
     * @return Paginator
     */
    public function getHighestRanked(ListingSearchRequest $listingSearchRequest, $limit, $locale)
    {
        $queryBuilder = $this->getRepository()->getFindByHighestRankingQueryBuilder($limit, $locale);

        $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
        $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH_HIGH_RANK_QUERY, $event);
        $queryBuilder = $event->getQueryBuilder();

        try {
            $query = $queryBuilder->getQuery();
            $query->setHydrationMode(Query::HYDRATE_ARRAY);
            $query->useResultCache(true, 21600, 'getHighestRanked');

            return new Paginator($query);//Important to manage limit
        } catch (NoResultException $e) {
            return null;
        }
    }


    /**
     * getListingsByIds returns the listings, depending upon ids provided
     *
     * @param ListingSearchRequest $listingSearchRequest
     * @param array                $ids
     * @param int                  $page
     * @param string               $locale
     * @param array                $idsExcluded
     * @param int                  $maxPerPage
     *
     * @return Paginator|null
     */
    public function getListingsByIds(
        $listingSearchRequest,
        $ids,
        $page,
        $locale,
        array $idsExcluded = array(),
        $maxPerPage = null
    ) {
        // Remove the current listing id from the similar listings
        $ids = array_diff($ids, $idsExcluded);

        $queryBuilder = $this->getRepository()->getFindQueryBuilder();

        //Where
        $queryBuilder
            ->where('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->andWhere('l.id IN (:ids)')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', BaseListing::STATUS_PUBLISHED)
            ->setParameter('ids', $ids);

        $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
        $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH_BY_IDS_QUERY, $event);
        $queryBuilder = $event->getQueryBuilder();

        if ($maxPerPage === null) {
            //Pagination
            if ($page) {
                $queryBuilder->setFirstResult(($page - 1) * $this->maxPerPage);
            }

            $queryBuilder->setMaxResults($this->maxPerPage);
        }

        //Query
        $query = $queryBuilder->getQuery();

        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        return new Paginator($query);
    }

    /**
     * @return int
     */
    public function getListingDefaultStatus(): int
    {
        return $this->listingDefaultStatus;
    }


    /**
     *
     * @return ListingRepository
     */
    public function getRepository(): ListingRepository
    {
        return $this->em->getRepository('CocoricoCoreBundle:Listing');
    }

}
