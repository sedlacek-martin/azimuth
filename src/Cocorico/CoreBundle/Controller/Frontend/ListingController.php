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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingNewType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listing controller.
 *
 * @Route("/listing")
 */
class ListingController extends Controller
{
    /**
     * Creates a new Listing entity.
     *
     * @Route("/new", name="cocorico_listing_new")
     *
     * @Security("not has_role('ROLE_SUPER_ADMIN') and has_role('ROLE_USER')")
     *
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws RuntimeException
     * @throws AccessDeniedException
     */
    public function newAction(Request $request)
    {
        $formHandler = $this->get('cocorico.form.handler.listing');

        $listing = $formHandler->init();
        $form = $this->createCreateForm($listing);
        $success = $formHandler->process($form);

        if ($success) {
            $url = $this->generateUrl(
                'cocorico_dashboard_listing_edit_presentation',
                ['id' => $listing->getId()]
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.new.success', [], 'cocorico_listing')
            );

            return $this->redirect($url);
        }

        return $this->render(
            'CocoricoCoreBundle:Frontend/Listing:new.html.twig',
            [
                'listing' => $listing,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Creates a form to create a Listing entity.
     *
     * @param Listing $listing The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            ListingNewType::class,
            $listing,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_listing_new'),
            ]
        );

        return $form;
    }

    /**
     * Finds and displays a Listing entity.
     *
     * @Route("/{slug}/show", name="cocorico_listing_show", requirements={
     *      "slug" = "[a-z0-9-]+$"
     * })
     * @Method("GET")
     * @Security("is_granted('view', listing)")
     * @ParamConverter("listing", class="Cocorico\CoreBundle\Entity\Listing", options={"repository_method" = "findOneBySlug"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return Response
     */
    public function showAction(Request $request, Listing $listing = null)
    {
        if ($this->getUser() == null && (!$listing->isPublic() || !$listing->isCertified())) {
            $this->createAccessDeniedException();
        }

        if ($redirect = $this->handleSlugChange($listing, $request->get('slug'))) {
            return $redirect;
        }

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addListingShowItems($request, $listing);

        return $this->render(
            'CocoricoCoreBundle:Frontend/Listing:show.html.twig',
            [
                'listing' => $listing,
            ]
        );
    }

    /**
     * Handle listing slug change 301 redirection
     *
     * @param Listing $listing
     * @param         $slug
     * @return bool|RedirectResponse
     */
    private function handleSlugChange(Listing $listing, $slug)
    {
        if ($slug != $listing->getSlug()) {
            return $this->redirect(
                $this->generateUrl('cocorico_listing_show', ['slug' => $listing->getSlug()]),
                301
            );
        }

        return false;
    }
}
