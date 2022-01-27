<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\CountryInformation;
use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Entity\PageAccess;
use Cocorico\CoreBundle\Repository\CountryInformationRepository;
use Cocorico\CoreBundle\Repository\MemberOrganizationRepository;
use Cocorico\CoreBundle\Utils\CountryUtils;
use Cocorico\CoreBundle\Utils\PHP;
use Cocorico\PageBundle\Entity\Page;
use Cocorico\PageBundle\Repository\PageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page frontend controller.
 *
 * @Route("/page")
 */
class PageController extends Controller
{
    /**
     * @Route("/map", name="cocorico_page_map")
     *
     * @Method("GET")
     *
     * @param Request $request
     * @return Response|null
     */
    public function showMapAction(Request  $request): ?Response
    {
        $file = $this->getParameter('cocorico.listing_count');

        $jsonData = @file_get_contents($file);

        if ($jsonData === false) {
            $jsonData = '[]';
        }

        return $this->render('@CocoricoPage/Frontend/Page/map.html.twig', [
            'listingCount' => $jsonData,
        ]);
    }

    /**
     * @Route("/find/{slug}", name="cocorico_page_find")
     *
     * @Method("GET")
     * @param Request $request
     * @param string $slug Country ISO code
     * @return JsonResponse
     */
    public function getState(Request $request, string $slug)
    {
        // this is here to prevent errors regarding CORS error
        header('Access-Control-Allow-Origin: *');

        $em = $this->getDoctrine()->getManager();

        /** @var CountryInformationRepository $countryInfoRepository */
        $countryInfoRepository = $em->getRepository(CountryInformation::class);
        $memberOrganizationRepository = $em->getRepository(MemberOrganization::class);

        /** @var CountryInformation $country */
        $country = $countryInfoRepository->findOneBy(['country' => $slug]);

        /** @var MemberOrganization[] $memberOrganizations */
        $memberOrganizations = $memberOrganizationRepository->findBy(['country' => $slug]);

        $countryDescription = $country ? $country->getDescription() : '';

        return new JsonResponse([
            'found' => (bool) $country,
            'memberOrganizationFound' => count($memberOrganizations) > 0,
            'countryName' => CountryUtils::getCountryName($slug),
            'countryText' => $countryDescription,
            'countryCode' => $slug,
        ]);
    }

    /**
     * @Route("/member-organizations/{slug}", name="cocorico_member_organizations_find")
     *
     * @Method("GET")
     * @param Request $request
     * @param string $slug Country ISO code
     * @return Response|null
     */
    public function memberOrganizationAction(Request $request, string $slug): ?Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MemberOrganizationRepository $memberOrganizationRepository */
        $memberOrganizationRepository = $em->getRepository(MemberOrganization::class);

        /** @var MemberOrganization[] $memberOrganizations */
        $memberOrganizations = $memberOrganizationRepository->findBy(['country' => $slug]);
        $countryName = (reset($memberOrganizations))->getCountryName();

        return $this->render('@CocoricoPage/Frontend/Page/member_organizations.html.twig', [
            'memberOrganizations' => $memberOrganizations,
            'countryName' => $countryName,
        ]);
    }

    /**
     * show page depending upon the slug available.
     *
     * @Route("/{slug}", name="cocorico_page_show")
     *
     * @Method("GET")
     *
     * @param  Request $request
     * @param  string  $slug
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function showAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PageRepository $page */
        $page = $em->getRepository('CocoricoPageBundle:Page')->findOneBySlug(
            $slug,
            $request->getLocale()
        );
        if (!$page) {
            throw new NotFoundHttpException(sprintf('%s page not found.', $slug));
        }

        $access = PageAccess::create($request->get('_route'), $this->getUser(), $slug);
        $em->persist($access);
        $em->flush();

        PHP::log($request->getHttpHost());

        return $this->render(
            '@CocoricoPage/Frontend/Page/show.html.twig',
            [
                'page' => $page,
            ]
        );
    }
}
