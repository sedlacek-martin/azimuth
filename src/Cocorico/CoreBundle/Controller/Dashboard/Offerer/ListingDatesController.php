<?php

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditDatesType;
use DateInterval;
use DateTime;
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
class ListingDatesController extends Controller
{
    /**
     * Edits Listing presentation.
     *
     * @Route("/{id}/edit_dates", name="cocorico_dashboard_listing_edit_dates", requirements={"id" = "\d+"})
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
    public function editDatesAction(Request $request, Listing $listing)
    {
        $translator = $this->get('translator');
        $editForm = $this->createEditDatesForm($listing);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($listing->getExpiryDate() > (new DateTime())->add(new DateInterval("P1Y"))) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $translator->trans('listing.edit.error', array(), 'cocorico_listing')
                );
                
                return $this->redirectToRoute(
                    'cocorico_dashboard_listing_edit_dates',
                    array('id' => $listing->getId())
                );
            }

            $this->get("cocorico.listing.manager")->save($listing);

            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('listing.edit.success', array(), 'cocorico_listing')

            );

            return $this->redirectToRoute(
                'cocorico_dashboard_listing_edit_dates',
                array('id' => $listing->getId())
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:edit_dates.html.twig',
            array(
                'listing' => $listing,
                'form' => $editForm->createView()
            )
        );

    }

    /**
     * Creates a form to edit a Listing entity.
     *
     * @param Listing $listing The entity
     *
     * @return Form The form
     */
    private function createEditDatesForm(Listing $listing): Form
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            ListingEditDatesType::class,
            $listing,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_dates',
                    array('id' => $listing->getId())
                ),
                'method' => 'POST',
            )
        );

        return $form;
    }


}