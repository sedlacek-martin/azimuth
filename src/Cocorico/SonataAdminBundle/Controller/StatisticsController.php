<?php

namespace Cocorico\SonataAdminBundle\Controller;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Entity\PageAccess;
use Cocorico\CoreBundle\Entity\UserLogin;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\CoreBundle\Repository\MemberOrganizationRepository;
use Cocorico\CoreBundle\Repository\PageAccessRepository;
use Cocorico\CoreBundle\Repository\UserLoginRepository;
use Cocorico\GeoBundle\Entity\Country;
use Cocorico\GeoBundle\Repository\CountryRepository;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\MessageBundle\Repository\MessageRepository;
use Cocorico\MessageBundle\Repository\ThreadRepository;
use Cocorico\SonataAdminBundle\Form\Type\StatisticsFilterType;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/statistics/")
 */
class StatisticsController extends Controller
{
    /**
     * @Route("", name="cocorico_admin__statistics")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request): ?Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You are not allowed to visit this page');
        }

        $from = $to = null;

        $form = $this->createForm(StatisticsFilterType::class, null, [
            'action' => $this->generateUrl('cocorico_admin__statistics'),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $from = $data['from'] ? \DateTime::createFromFormat('m/d/Y H:i', $data['from'] . '00:00') : null;
            $to = $data['to'] ? \DateTime::createFromFormat('m/d/Y H:i', $data['to'] . '00:00') : null;
        }

        $em = $this->getDoctrine()->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);
        /** @var CountryRepository $countryRepository */
        $countryRepository = $em->getRepository(Country::class);
        /** @var MemberOrganizationRepository $memberOrganizationRepository */
        $memberOrganizationRepository = $em->getRepository(MemberOrganization::class);
        /** @var PageAccessRepository $pageAccessRepository */
        $pageAccessRepository = $em->getRepository(PageAccess::class);
        /** @var UserLoginRepository $userLoginRepository */
        $userLoginRepository = $em->getRepository(UserLogin::class);
        /** @var ListingRepository $listingRepository */
        $listingRepository = $em->getRepository(Listing::class);
        /** @var MessageRepository $messageRepository */
        $messageRepository = $em->getRepository(Message::class);
        /** @var ThreadRepository $messageThreadRepository */
        $messageThreadRepository = $em->getRepository(Thread::class);

        $facilitatorCount = count($userRepository->findByRoles('ROLE_FACILITATOR'));
        $activatorCount = count($userRepository->findByRoles('ROLE_ACTIVATOR'));
        $superAdminCount = count($userRepository->findByRoles('ROLE_SUPER_ADMIN'));
        $adminCount = count($userRepository->findByRoles('ROLE_FACILITATOR', 'ROLE_ACTIVATOR', 'ROLE_SUPER_ADMIN'));

        $countryCount = $countryRepository->countAll();

        $memberOrganizationCount = $memberOrganizationRepository->countAll();

        $userCount = $userRepository->getTotalCount();
        $activatedUserCount = $userRepository->getActivatedCount();

        $verificationTimeData = $userRepository->getAverageActivationTime($from, $to);
        $verificationTimeSeconds = $verificationTimeData['cnt'] ? round($verificationTimeData['total'] / $verificationTimeData['cnt']) : 0;

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$verificationTimeSeconds");
        $diff = $dtF->diff($dtT);
        if ($diff->days > 0) {
            $verificationTime = $diff->format('%a days and %h hours');
        } elseif ($diff->h > 0) {
            $verificationTime = $diff->format('%h hours and %i minutes');
        } else {
            $verificationTime = $diff->format('%i minutes and %s seconds');
        }

        $loginCount = $userLoginRepository->countAll($from, $to);

        $faqAccessCount = $pageAccessRepository->countAll('faq', $from, $to);

        $listingCount = $listingRepository->countAll($from, $to);

        $threadCount = $messageThreadRepository->countAll($from, $to);
        $messageCount = $messageRepository->countAll($from, $to);

        return $this->render('CocoricoSonataAdminBundle::Statistics/index.html.twig', [
            'form' => $form->createView(),
            'facilitatorCount' => $facilitatorCount,
            'activatorCount' => $activatorCount,
            'adminCount' => $adminCount,
            'superAdminCount' => $superAdminCount,
            'countryCount' => $countryCount,
            'memberOrganizationCount' => $memberOrganizationCount,
            'userCount' => $userCount,
            'activatedUserCount' => $activatedUserCount,
            'verificationTime' => $verificationTime,
            'loginCount' => $loginCount,
            'faqAccessCount' => $faqAccessCount,
            'listingCount' => $listingCount,
            'threadCount' => $threadCount,
            'messageCount' => $messageCount,
        ]);
    }
}
