<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\BaseListing;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\CoreBundle\Utils\HtmlUtils;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Handle Listing Form
 */
class ListingFormHandler
{
    protected $request;
    protected $listingManager;
    /** @var User|null */
    private $user = null;

    /**
     * ListingFormHandler constructor.
     * @param TokenStorage         $securityTokenStorage
     * @param AuthorizationChecker $securityAuthChecker
     * @param RequestStack         $requestStack
     * @param ListingManager       $listingManager
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException
     */
    public function __construct(
        TokenStorage $securityTokenStorage,
        AuthorizationChecker $securityAuthChecker,
        RequestStack $requestStack,
        ListingManager $listingManager
    ) {
        if ($securityAuthChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->user = $securityTokenStorage->getToken()->getUser();
        }
        $this->request = $requestStack->getCurrentRequest();
        $this->listingManager = $listingManager;

    }


    /**
     * @return Listing
     * @throws AccessDeniedException
     */
    public function init()
    {
        //todo: move to ListingManager->initListing() see BookingManager->initBooking
        $listing = new Listing();
        $listing->setUser($this->user);
        $listing = $this->addImages($listing);
        $listing = $this->addCategory($listing);

        return $listing;
        
    }

    /**
     * Process form
     *
     * @param Form $form
     *
     * @return bool
     * @throws RuntimeException
     */
    public function process($form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST') && $form->isValid()) {
            return $this->onSuccess($form);
        }

        return false;
    }

    /**
     * @param Form $form
     * @return bool
     * @throws RuntimeException
     */
    private function onSuccess(Form $form)
    {
        /** @var Listing $listing */
        $listing = $form->getData();

        if ($listing->getImages()->isEmpty()) {
            $this->listingManager->addDefaultImage($listing);
        }

        foreach ($listing->getTranslations() as $translation) {
            $translation->setDescription(HtmlUtils::purifyBasicHtml($translation->getDescription()));
        }

        $this->listingManager->save($listing);

        return true;
    }


    /**
     * @param  Listing $listing
     * @throws AccessDeniedException
     * @return Listing
     */
    private function addImages(Listing $listing)
    {
        //Files to upload
        $imagesUploaded = $this->request->request->get("listing");
        $imagesUploaded = $imagesUploaded["image"]["uploaded"];

        if ($imagesUploaded) {
            $imagesUploadedArray = explode(",", trim($imagesUploaded, ","));
            $listing = $this->listingManager->addImages(
                $listing,
                $imagesUploadedArray
            );
        }

        return $listing;
    }

    /**
     * Add selected categories and corresponding fields values from post parameters while listing deposit
     *
     * @param  Listing $listing
     * @return Listing
     */
    public function addCategory(Listing $listing)
    {
        $data = $this->request->request->get("listing_categories");

        $listingCategory = $data["category"] ?? null;
        $listingType = $data['type'] ?? BaseListing::OFFER;


        $listing->setType($listingType);

        if ($data) {
            $listing = $this->listingManager->addCategory(
                $listing,
                $listingCategory
            );
        }

        return $listing;
    }

}