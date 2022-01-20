<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Event\ListingSearchActionEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingSearchHomeType;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingSearchNavType;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingSearchResultType;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingSearchType;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Model\Manager\ListingSearchManager;
use Cocorico\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ListingSearchController extends Controller
{
    /**
     * Listings search result.
     *
     * @Route("/listing/search_result", name="cocorico_listing_search_result")
     * @Route("/listing/search_map", name="cocorico_listing_search_result_map")
     * @Method("GET")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        //For drag map mode
        $isXmlHttpRequest = $request->isXmlHttpRequest();
        $actualRoute = $request->attributes->get('_route');

        $viewData = $this->searchBaseAction($request, $isXmlHttpRequest, $actualRoute);
        return $this->render(
            $isXmlHttpRequest ?
                '@CocoricoCore/Frontend/ListingResult/result_ajax.html.twig' :
                '@CocoricoCore/Frontend/ListingResult/result.html.twig',
                array_merge($viewData, [
//                    'horizontal_map_layout' => $actualRoute === "cocorico_listing_search_result_map",$actualRoute === "cocorico_listing_search_result_map",
                    'horizontal_map_layout' => true,
                    'route' => $request->attributes->get('_route'),
                ])
        );
    }

    /**
     * @param Request $request
     * @Route("/listing/map", name="cocorico_listing_map")
     */
    public function mapAction(Request $request)
    {
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->get('cocorico.listing_search_request');
        $listingSearchRequest->getLocation()->setAddress("Czechia");
        $listingSearchRequest->getLocation()->setViewport("((41.305296366930165, -12.741266900788446), (55.722966020356786, 39.993108099211554))");
        $listingSearchRequest->getLocation()->setAddressType("country");

        $form = $this->createSearchResultForm($listingSearchRequest, "cocorico_listing_search_result_map");

        $page = $request->get('page');
        if ($page) {
            $listingSearchRequest->setPage($page);
        }

        $results = $this->get("cocorico.listing_search.manager")->searchAll(
            $request->getLocale(),
            $listingSearchRequest
        );
        $nbListings = $results->count();
        $listings = $results->getIterator();
        $markers = $this->getMarkers($request, $results, $listings);

//        //Breadcrumbs
//        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
//        $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

        //Add params to view through event listener
        $event = new ListingSearchActionEvent($request);
        $this->get('event_dispatcher')->dispatch(ListingSearchEvents::LISTING_SEARCH_ACTION, $event);
        $extraViewParams = $event->getExtraViewParams();

        return $this->render(
            '@CocoricoCore/Frontend/ListingResult/result.html.twig',
            array_merge(
            array(
                'horizontal_map_layout' => true,
                'route' => "cocorico_listing_search_result_map",
                'form' => $form->createView(),
                'listings' => $listings,
                'nb_listings' => $nbListings,
                'markers' => $markers['markers'],
                'listing_search_request' => $listingSearchRequest,
                'pagination' => array(
                    'page' => $listingSearchRequest->getPage(),
                    'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                ),
            ),
            $extraViewParams
        ));


    }

    protected function searchBaseAction(Request $request, bool $isXmlHttpRequest, string $searchRoute = 'cocorico_listing_search_result')
    {
        $markers = array('listingsIds' => array(), 'markers' => array());
        $listings = new \ArrayIterator();
        $nbListings = 0;

        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->get('cocorico.listing_search_request');
        $isXmlHttpRequest ? $listingSearchRequest->setSortBy('distance') : null;
        $form = $this->createSearchResultForm($listingSearchRequest, $searchRoute);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ListingSearchRequest $listingSearchRequest */
            $listingSearchRequest = $form->getData();

            if ($request->get("_route") == "cocorico_listing_search_result_map" &&
                $listingSearchRequest->getLocation()->getCountry() == null) {
                /** @var User $user */
                $user = $this->getUser();
                $listingSearchRequest->getLocation()->setCountry($user->getCountry());
            }

            /** @var ListingSearchManager $listingSearchManager */
            $listingSearchManager = $this->get("cocorico.listing_search.manager");
            $results = $listingSearchManager->search(
                $listingSearchRequest,
                $request->getLocale()
            );

            $nbListings = $results->count();
            $listings = $results->getIterator();
            $markers = $this->getMarkers($request, $results, $listings);

            //Persist similar listings id
            $listingSearchRequest->setSimilarListings($markers['listingsIds']);

            //Persist listing search request in session
            !$isXmlHttpRequest ? $this->get('session')->set('listing_search_request', $listingSearchRequest) : null;
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    /** @Ignore */
                    $this->get('translator')->trans($error->getMessage(), $error->getMessageParameters(), 'cocorico')
                );
            }
        }

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

        //Add params to view through event listener
        $event = new ListingSearchActionEvent($request);
        $this->get('event_dispatcher')->dispatch(ListingSearchEvents::LISTING_SEARCH_ACTION, $event);
        $extraViewParams = $event->getExtraViewParams();


        return array_merge(
            array(
                'form' => $form->createView(),
                'listings' => $listings,
                'nb_listings' => $nbListings,
                'markers' => $markers['markers'],
                'listing_search_request' => $listingSearchRequest,
                'pagination' => array(
                    'page' => $listingSearchRequest->getPage(),
                    'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                ),
            ),
            $extraViewParams
        );
    }

    /**
     * @param ListingSearchRequest $listingSearchRequest
     *
     * @param string $searchRoute
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchResultForm(ListingSearchRequest $listingSearchRequest, $searchRoute = 'cocorico_listing_search_result')
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchResultType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl($searchRoute),
            )
        );

        return $form;
    }

    /**
     * Get Markers
     *
     * @param  Request        $request
     * @param  Paginator      $results
     * @param  \ArrayIterator $resultsIterator
     *
     * @return array
     *          array['markers'] markers data
     *          array['listingsIds'] listings ids
     */
    protected function getMarkers(Request $request, $results, $resultsIterator)
    {
        //We get listings id of current page to change their marker aspect on the map
        $resultsInPage = array();
        foreach ($resultsIterator as $i => $result) {
            $resultsInPage[] = $result[0]['id'];
        }

        //We need to display all listings (without pagination) of the current search on the map
        $results->getQuery()->setFirstResult(null);
        $results->getQuery()->setMaxResults(null);
        $nbResults = $results->count();

        $imagePath = ListingImage::IMAGE_FOLDER;
        $locale = $request->getLocale();
        $liipCacheManager = $this->get('liip_imagine.cache.manager');
        $listingSession = array_key_exists('CocoricoListingSessionBundle', $this->getParameter('kernel.bundles'));
        $markers = $listingsIds = array();

        foreach ($results->getIterator() as $i => $result) {
            $listing = $result[0];
            $listingsIds[] = $listing['id'];
            $isInCurrentPage = in_array($listing['id'], $resultsInPage);

            //Image
            $imageName = count($listing['images']) ? $listing['images'][0]['name'] : ListingImage::IMAGE_DEFAULT;
            $image = $liipCacheManager->getBrowserPath($imagePath . $imageName, 'listing_medium', array());

            //Duration
            $duration = null;
            if ($listingSession && array_key_exists('duration', $listing)) {
                /** @var \DateTime $duration */
                $duration = $listing['duration'];
                $duration = '(' . $duration->format('H\hi') . ')';
            }

            //Categories
            $categories = '';
            $pin = '';
            if (count($listing['category'])) {
                $categories = $listing['category']['translations'][$locale]['name'];
                $pin = $listing['category']['pin'];
                $pinImage = isset($pin) ? $pin['imagePath'] : '/images/pin.png';
            }

            //Allow to group markers with same location
            $locIndex = $listing['location']['coordinate']['lat'] . "-" . $listing['location']['coordinate']['lng'];
            $markers[$locIndex][] = array(
                'id' => $listing['id'],
                'lat' => $listing['location']['coordinate']['latRandom'],
                'lng' => $listing['location']['coordinate']['lngRandom'],
                'title' => $listing['translations'][$locale]['title'],
                'category' => $categories,
                'pinImage' => $pinImage,
                'image' => $image,
                'duration' => $duration,
                'certified' => $listing['certified'] ? 'certified' : 'hidden',
                'url' => $this->generateUrl(
                    'cocorico_listing_show',
                    array('slug' => $listing['translations'][$locale]['slug'])
                ),
                'zindex' => $isInCurrentPage ? 2 * $nbResults - $i : $i,
                'opacity' => $isInCurrentPage ? 1 : 0.4,
            );
        }

        return array(
            'markers' => $markers,
            'listingsIds' => $listingsIds
        );
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchHomeFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchHomeForm($listingSearchRequest);
        
        return $this->render(
            '@CocoricoCore/Frontend/Home/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    public function searchNavFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchNavForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/Home/form_nav.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createSearchHomeForm(ListingSearchRequest $listingSearchRequest, $searchRoute = 'cocorico_listing_search_result')
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchHomeType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl($searchRoute),
            )
        );

        return $form;
    }

    private function createSearchNavForm(ListingSearchRequest $listingSearchRequest, $searchRoute = 'cocorico_listing_search_result')
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchNavType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl($searchRoute),
            )
        );

        return $form;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/Common/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }


    /**
     * similarListingAction will list out the listings which are almost similar to what has been
     * searched.
     *
     * @Route("/listing/similar_result/{id}", name="cocorico_listing_similar")
     * @Method("GET")
     *
     * @param  Request $request
     * @param int      $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function similarListingAction(Request $request, $id = null)
    {
        $results = new ArrayCollection();
        $listingSearchRequest = $this->getListingSearchRequest();
        $ids = ($listingSearchRequest) ? $listingSearchRequest->getSimilarListings() : array();
        if ($listingSearchRequest && count($ids) > 0) {
            $results = $this->get("cocorico.listing_search.manager")->getListingsByIds(
                $listingSearchRequest,
                $ids,
                null,
                $request->getLocale(),
                array($id)
            );
        }

        return $this->render(
            '@CocoricoCore/Frontend/Listing/similar_listing.html.twig',
            array(
                'results' => $results
            )
        );
    }

    /**
     * @return ListingSearchRequest
     */
    protected function getListingSearchRequest()
    {
        $session = $this->get('session');
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $session->has('listing_search_request') ?
            $session->get('listing_search_request') :
            $this->get('cocorico.listing_search_request');

        return $listingSearchRequest;
    }

}
