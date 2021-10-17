<?php

namespace Cocorico\SonataAdminBundle\Controller;

use Cocorico\CoreBundle\Entity\Announcement;
use Cocorico\CoreBundle\Entity\AnnouncementToUser;
use Cocorico\CoreBundle\Repository\AnnouncementToUserRepository;
use Cocorico\SonataAdminBundle\Form\Type\AnnouncementType;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/announcement")
 */
class AnnouncementController extends Controller
{
    /**
     * @Route("/create", name="announcement_create")
     * @Route("/{id}/edit", name="announcement_edit", requirements={"id" = "\d+"})
     * @Method({"GET", "POST"})
     */
    public function addNewAction(Request $request, Announcement $announcement = null): ?Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException("You are not allowed to visit this page");
        }

        $action = $announcement
            ? $this->generateUrl('announcement_edit', ['id' => $announcement->getId()])
            : $this->generateUrl('announcement_create');

        if ($announcement === null) {
            $announcement = new Announcement();
        }

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(AnnouncementType::class, $announcement, [
            'action' => $action,
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = 'flash_action_announcement_saved';
            $em->persist($announcement);
            // only for newly created announcements
            if ($announcement->getId() === null) {
                $message = 'flash_action_announcement_send';
                $memberOrganizations = $form->get('memberOrganizations')->getData();
                $includeAdmins = $form->get('includeAdmins')->getData();
                /** @var UserRepository $userRepository */
                $userRepository = $em->getRepository(User::class);
                /** @var AnnouncementToUserRepository $userAnnouncementRepository */
                $userAnnouncementRepository = $em->getRepository(AnnouncementToUser::class);

                $i = 0;

                $insertUserAnnouncement = function (Announcement $announcement, User $user, bool $includeAdmins, int $batchSize = 100) use (&$i, $em, $userAnnouncementRepository) {
                    if (!$includeAdmins && count(array_intersect(User::ADMIN_ROLES, $user->getRoles())) > 0) {
                        return;
                    }
                    $userAnnouncement = new AnnouncementToUser($announcement, $user);
                    $userAnnouncementRepository->clearCache($user->getId());
                    $em->persist($userAnnouncement);
                    if ($i++ % $batchSize == 0) {
                        $em->flush();
                    }
                };

                if (count($memberOrganizations) > 0) {
                    foreach ($memberOrganizations as $memberOrganization) {
                        /** @var User[] $users */
                        $users = $userRepository->findBy(['memberOrganization' => $memberOrganization]);
                        foreach ($users as $user) {
                            $insertUserAnnouncement($announcement, $user, $includeAdmins);
                        }
                    }
                } else {
                    $userQuery = $userRepository->createQueryBuilder('user')->getQuery();
                    foreach ($userQuery->iterate() as $row) {
                        $user = $row[0];
                        $insertUserAnnouncement($announcement, $user, $includeAdmins);
                    }
                }
            }

            $em->flush();

            $this->addFlash(
                'sonata_flash_success',
                $this->get('translator')->trans($message, [], 'SonataAdminBundle')
            );

            return $this->redirectToRoute('announcement_list');
        }

        return $this->render('CocoricoSonataAdminBundle::Announcement/new.html.twig', [
            'form' => $form->createView(),
            'announcement' => $announcement,
        ]);

    }

}