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
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditDescriptionType;
use Cocorico\CoreBundle\Utils\HtmlUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingPresentationController extends Controller
{
    /**
     * Edits Listing presentation.
     *
     * @Route("/{id}/edit_presentation", name="cocorico_dashboard_listing_edit_presentation", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"GET", "PUT", "POST"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return RedirectResponse|Response
     */
    public function editPresentationAction(Request $request, Listing $listing)
    {
        $translator = $this->get('translator');
        $editForm = $this->createEditPresentationForm($listing);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            foreach ($listing->getTranslations() as $translation) {
                $translation->setDescription(HtmlUtils::purifyBasicHtml($translation->getDescription()));
            }

            $this->get('cocorico.listing.manager')->save($listing);

            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('listing.edit.success', [], 'cocorico_listing')

            );

            return $this->redirectToRoute(
                'cocorico_dashboard_listing_edit_presentation',
                ['id' => $listing->getId()]
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:edit_presentation.html.twig',
            [
                'listing' => $listing,
                'form' => $editForm->createView(),
            ]
        );
    }

    /**
     * Creates a form to edit a Listing entity.
     *
     * @param Listing $listing The entity
     *
     * @return Form The form
     */
    private function createEditPresentationForm(Listing $listing): Form
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            ListingEditDescriptionType::class,
            $listing,
            [
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_presentation',
                    ['id' => $listing->getId()]
                ),
                'method' => 'POST',
            ]
        );

        return $form;
    }
}
