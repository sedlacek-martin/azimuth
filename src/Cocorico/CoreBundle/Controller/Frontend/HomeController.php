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

use Cocorico\CoreBundle\Entity\Announcement;
use Cocorico\CoreBundle\Entity\AnnouncementToUser;
use Cocorico\CoreBundle\Repository\AnnouncementRepository;
use Cocorico\CoreBundle\Repository\AnnouncementToUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController
 *
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="cocorico_home")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $userAuthenticated = $this->getUser() !== null;
        $listings = $this->get("cocorico.listing_search.manager")->getHighestRanked(
            $this->get('cocorico.listing_search_request'),
            6,
            $request->getLocale(),
            !$userAuthenticated
        );

        return $this->render(
            'CocoricoCoreBundle:Frontend\Home:index.html.twig',
            array(
                'listings' => $listings->getIterator(),
            )
        );
    }

    /**
     * @Route("/get-announcements", name="cocorico_dashboard_announcements")
     */
    public function getAnnouncements(Request $request)
    {
        $response = ['count' => 0];
        if (true or $request->isXmlHttpRequest()) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            /** @var AnnouncementToUserRepository $userAnnouncementRepository */
            $userAnnouncementRepository = $em->getRepository(AnnouncementToUser::class);
            /** @var AnnouncementToUser[] $userAnnouncements */
            $userAnnouncements = $userAnnouncementRepository->getAnnouncementsWithCache($user);
            $response['count'] = count(($userAnnouncements));
            $response['announcements'] = [];
            foreach ($userAnnouncements as $userAnnouncement) {
                $announcement = $userAnnouncement->getAnnouncement();
                $announcementData = [];
                $announcementData['heading'] = $announcement->getHeading();
                $announcementData['content'] = $announcement->getContent();
                $announcementData['description'] = $announcement->getShortDescription();
                $response['announcements'][$announcement->getId()] = $announcementData;
            }
            $view = $this->render('CocoricoCoreBundle:Frontend\Home:_announcements.html.twig', [
                'userAnnouncements' => $userAnnouncements,
            ])->getContent();
            $response['view'] = $view;
            $response['ok'] = true;
        }


        return JsonResponse::create($response);
    }

    /**
     * @Route("/announcement/show/{id}", name="cocorico_announcement_show")
     *
     * @param AnnouncementToUser $userAnnouncement
     * @return Response|null
     */
    public function showAnnouncementAction(AnnouncementToUser $userAnnouncement): ?Response
    {
        $em = $this->getDoctrine()->getManager();

        $userAnnouncement->setDisplayed(true);
        $userAnnouncement->setDisplayedAt(new \DateTime());

        $em->persist($userAnnouncement);
        $em->flush();

        /** @var AnnouncementToUserRepository $userAnnouncementRepository */
        $userAnnouncementRepository = $em->getRepository(AnnouncementToUser::class);
        $userAnnouncementRepository->clearCache($userAnnouncement->getUser()->getId());

        return $this->render('CocoricoCoreBundle:Frontend\Home:announcement_show.html.twig', [
            'announcement' => $userAnnouncement->getAnnouncement(),
        ]);
    }

    /**
     * @Route("/announcement/dismiss/{id}", name="cocorico_announcement_dismiss")
     *
     * @param Request $request
     * @param AnnouncementToUser $userAnnouncement
     * @return JsonResponse
     */
    public function dismissAnnouncementAction(Request $request, AnnouncementToUser $userAnnouncement): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            $userAnnouncement->setDismissed(true);
            $userAnnouncement->setDismissedAt(new \DateTime());

            $em->persist($userAnnouncement);
            $em->flush();

            /** @var AnnouncementToUserRepository $userAnnouncementRepository */
            $userAnnouncementRepository = $em->getRepository(AnnouncementToUser::class);
            $userAnnouncementRepository->clearCache($userAnnouncement->getUser()->getId());
        }

        return JsonResponse::create();
    }


    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rssFeedsAction()
    {
        $feed = $this->getParameter('cocorico.home_rss_feed');
        if (!$feed) {
            return new Response();
        }

        $cacheTime = 3600 * 12;
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir . '/rss-home-feed.json';
        $timeDif = @(time() - filemtime($cacheFile));
        $renderFeeds = array();

        if (file_exists($cacheFile) && $timeDif < $cacheTime) {
            $renderFeeds = json_decode(@file_get_contents($cacheFile), true);
        } else {
            $options = array(
                'http' => array(
                    'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
                    'timeout' => 5,
                ),
            );

            $content = @file_get_contents($feed, false, stream_context_create($options));

            $feeds = array();
            if ($content) {
                try {
                    $feeds = new \SimpleXMLElement($content);
                    $feeds = $feeds->channel->xpath('//item');
                } catch (\Exception $e) {
                    // silently fail error
                }
            }

            /**
             * @var                    $key
             * @var  \SimpleXMLElement $feed
             */
            foreach ($feeds as $key => $feed) {
                $renderFeeds[$key]['title'] = (string)$feed->children()->title;
                $renderFeeds[$key]['pubDate'] = (string)$feed->children()->pubDate;
                $renderFeeds[$key]['link'] = (string)$feed->children()->link;
                $description = $feed->children()->description;
                $matches = [];
                preg_match('/src="([^"]+)"/', $description, $matches);
                if (count($matches)) {
                    $renderFeeds[$key]['image'] = str_replace('http:', '', $matches[1]);
                }
            }

            @file_put_contents($cacheFile, json_encode($renderFeeds));
        }


        return $this->render(
            'CocoricoCoreBundle:Frontend/Home:rss_feed.html.twig',
            array(
                'feeds' => $renderFeeds,
            )
        );
    }

}
