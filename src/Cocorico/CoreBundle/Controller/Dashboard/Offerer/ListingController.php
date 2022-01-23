<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditStatusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingController extends Controller
{
    /**
     * @param  Listing $listing
     * @return Response
     */
    public function statusIndexFormAction($listing)
    {
        $form = $this->createStatusForm($listing, 'index');

        return $this->render(
            '@CocoricoCore/Dashboard/Listing/form_status_index.html.twig',
            [
                'form' => $form->createView(),
                'listing' => $listing,
            ]
        );
    }

    /**
     * @param  Listing $listing
     * @return Response
     */
    public function statusNavSideFormAction($listing)
    {
        $form = $this->createStatusForm($listing, 'nav_side');

        return $this->render(
            '@CocoricoCore/Dashboard/Listing/form_status_nav_side.html.twig',
            [
                'form' => $form->createView(),
                'listing' => $listing,
            ]
        );
    }

    /**
     * @param Listing $listing
     * @param string  $view
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createStatusForm(Listing $listing, $view)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_status',
            ListingEditStatusType::class,
            $listing,
            [
                'method' => 'POST',
                'action' => $this->generateUrl(
                        'cocorico_dashboard_listing_edit_status',
                        ['id' => $listing->getId()]
                    ) . '?view=' . $view,
            ]
        );

        return $form;
    }

    /**
     * Edit Listing status.
     *
     * @Route("/{id}/edit_status", name="cocorico_dashboard_listing_edit_status", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editStatusAction(Request $request, Listing $listing)
    {
        $view = $request->get('view');

        $form = $this->createStatusForm($listing, $view);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();

        if ($formIsValid) {
            $listing = $this->get('cocorico.listing.manager')->save($listing);
            $this->addFormSuccessMessagesToFlashBag('status');
        }

        if ($request->isXmlHttpRequest()) {
            if ($view == 'index') {
                return $this->statusIndexFormAction($listing);
            } elseif ($view == 'nav_side') {
                return $this->statusNavSideFormAction($listing);
            }

            return new Response('View missing');
        }
        if (!$formIsValid) {
            $this->addFormErrorMessagesToFlashBag($form);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * Lists all Listing entities.
     *
     * @Route("/{page}", name="cocorico_dashboard_listing", defaults={"page" = 1 })
     *
     * @Method("GET")
     *
     * @param  Request $request
     * @param  int     $page
     *
     * @return Response
     */
    public function indexAction(Request $request, $page): Response
    {
        $listingManager = $this->get('cocorico.listing.manager');
        $listings = $listingManager->findByOwner(
            $this->getUser()->getId(),
            $request->getLocale(),
            Listing::$visibleStatus,
            $page
        );

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:index.html.twig',
            [
                'listings' => $listings,
                'pagination' => [
                    'page' => $page,
                    'pages_count' => ceil($listings->count() / $listingManager->maxPerPage),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all(),
                ],
            ]
        );
    }

    /**
     * Form Error
     *
     * @param $form
     */
    private function addFormErrorMessagesToFlashBag($form)
    {
        $this->get('cocorico.helper.global')->addFormErrorMessagesToFlashBag(
            $form,
            $this->get('session')->getFlashBag()
        );
    }

    /**
     * Form Success
     *
     * @param $type
     */
    private function addFormSuccessMessagesToFlashBag($type)
    {
        $session = $this->get('session');

        if ($type == 'price') {
            $session->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit_price.success', [], 'cocorico_listing')
            );
        } elseif ($type == 'status') {
            $session->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit_status.success', [], 'cocorico_listing')
            );
        }
    }

    /**
     * @param  Listing $listing
     * @return Response
     */
    public function completionNoticeAction(Listing $listing)
    {
        $listingCompletion = $listing->getCompletionInformations(
            $this->getParameter('cocorico.listing_img_min')
        );
        $userCompletion = $listing->getUser()->getCompletionInformation(
            $this->getParameter('cocorico.user_img_min'), 100
        );

        return $this->render(
            '@CocoricoCore/Dashboard/Listing/_completion_notice.html.twig',
            [
                'listing_id' => $listing->getId(),
                'listing_title' => $listingCompletion['title'],
                'listing_desc' => $listingCompletion['description'],
                'listing_image' => $listingCompletion['image'],
                'listing_characteristics' => $listingCompletion['characteristic'],
                'profile_photo' => $userCompletion['image'],
                'profile_desc' => $userCompletion['description'],
            ]
        );
    }
}
