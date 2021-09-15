<?php

namespace Cocorico\SonataAdminBundle\Controller;

use Cocorico\CoreBundle\Entity\CountryInformation;
use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Repository\CountryInformationRepository;
use Cocorico\SonataAdminBundle\Form\Type\ActivatorSettingsType;
use Cocorico\SonataAdminBundle\Form\Type\FacilitatorSettingsType;
use Cocorico\SonataAdminBundle\Form\Type\MoEditType;
use Cocorico\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Booking Dashboard controller.
 *
 * @Route("/action/")
 */
class CustomActionController extends Controller
{
    /**
     * @Route("", name="cocorico_admin__index")
     * @Method("GET")
     */
    public function indexAction()
    {
    }

    /**
     * @Route("dashboard", name="cocorico_admin__dashboard")
     * @Method("GET")
     */
    public function dashboardAction(): Response
    {

        return $this->render(
            'CocoricoSonataAdminBundle::CustomActions/dashboard.html.twig',
            []
        );
    }

    /**
     * @Route("edit-mo/{id}", name="cocorico_admin__edit_mo")
     * @Method({"GET", "POST"})
     */
    public function editMOInfoAction(MemberOrganization $memberOrganization, Request $request): ?Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();
            if ($user->getMemberOrganization()->getId() !== $memberOrganization->getId()) {
                throw $this->createAccessDeniedException("You are not allowed to edit this MO");
            }
        }

        $em = $this->getDoctrine()->getManager();
        /** @var CountryInformationRepository $countryInfoRepository */
        $countryInfoRepository = $em->getRepository(CountryInformation::class);
        $countryInfo = $countryInfoRepository->findByCountryCode($memberOrganization->getCountry());

        $form = $this->createForm(MoEditType::class, $memberOrganization, [
            'action' => $this->generateUrl('cocorico_admin__edit_mo', ['id' => $memberOrganization->getId()]),
            'country_description' => $countryInfo ? $countryInfo->getDescription() : '',
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if ($countryInfo === null) {
                $countryInfo = new CountryInformation();
                $countryInfo->setCountry($memberOrganization->getCountry());
            }
            $countryInfo->setDescription($form->get('countryDescription')->getData());

            $em->persist($memberOrganization);
            $em->persist($countryInfo);
            $em->flush();

        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/edit_mo.html.twig', [
                'memberOrganization' => $memberOrganization,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("activator/settings", name="cocorico_admin__activator_settings")
     * @Method({"GET", "POST"})
     */
    public function activatorSettingsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ACTIVATOR')) {
            throw $this->createAccessDeniedException("You are not allowed to edit activator settings");
        }

        /** @var User $user */
        $user = $this->getUser();
        $memberOrganization = $user->getMemberOrganization();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ActivatorSettingsType::class, $memberOrganization, [
            'action' => $this->generateUrl('cocorico_admin__activator_settings'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($memberOrganization);
            $em->flush();

            return $this->redirectToRoute('cocorico_admin__activator_settings');

        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/activator_settings.html.twig', [
                'memberOrganization' => $memberOrganization,
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * @Route("facilitator/settings", name="cocorico_admin__facilitator_settings")
     * @Method({"GET", "POST"})
     */
    public function facilitatorSettingsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_FACILITATOR')) {
            throw $this->createAccessDeniedException("You are not allowed to edit facilitator settings");
        }

        /** @var User $user */
        $user = $this->getUser();
        $memberOrganization = $user->getMemberOrganization();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(FacilitatorSettingsType::class, $memberOrganization, [
            'action' => $this->generateUrl('cocorico_admin__facilitator_settings'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($memberOrganization);
            $em->flush();

            return $this->redirectToRoute('cocorico_admin__facilitator_settings');

        }

        return $this->render('CocoricoSonataAdminBundle::CustomActions/facilitator_settings.html.twig', [
                'memberOrganization' => $memberOrganization,
                'form' => $form->createView(),
            ]
        );

    }
}