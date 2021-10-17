<?php

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Repository\MemberOrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * MO controller.
 *
 * @Route("/member-organization")
 */
class MemberOrganizationController extends Controller
{

    /**
     * @Route("/get-all/{country}", name="cocorico_member_organization__get_all_country")
     * @Method({"GET", "POST"})
     *
     * @param string $country
     * @return JsonResponse
     */
    public function getMemberOrganizationsAction(string $country): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        /** @var MemberOrganizationRepository $memberOrganizationRepository */
        $memberOrganizationRepository = $em->getRepository(MemberOrganization::class);

        /** @var MemberOrganization[] $memberOrganizations */
        $memberOrganizations = $memberOrganizationRepository->findBy(['country' => $country]);

        $data = [];
        foreach ($memberOrganizations as $memberOrganization) {
            $data[$memberOrganization->getId()] = [
                'name' => $memberOrganization->getName(),
                'id' => $memberOrganization->getId(),
            ];
        }

        return $this->json($data);
    }

}