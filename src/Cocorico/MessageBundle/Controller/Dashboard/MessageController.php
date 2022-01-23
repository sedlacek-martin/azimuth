<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Controller\Dashboard;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\MessageBundle\Event\MessageEvent;
use Cocorico\MessageBundle\Event\MessageEvents;
use Cocorico\MessageBundle\FormModel\NewThreadMessage;
use Cocorico\MessageBundle\Repository\MessageRepository;
use Cocorico\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\MessageBundle\Model\ParticipantInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends Controller
{
    /**
     * Lists all the available messages
     *
     * @Method("GET")
     * @Route("/{page}", name="cocorico_dashboard_message", requirements={"page" = "\d+"}, defaults={"page" = 1})
     *
     * @param Request $request
     * @param int $page
     * @return Response
     * @throws AccessDeniedException
     */
    public function indexAction(Request $request, $page)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof ParticipantInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $threadManager = $this->get('cocorico_message.thread_manager');
        /** @var Paginator $threads */
        $threads = $threadManager->getListingInboxThreads($user, $page);

        return $this->render(
            'CocoricoMessageBundle:Dashboard/Message:inbox.html.twig',
            [
                'threads' => $threads,
                'pagination' => [
                    'page' => $page,
                    'pages_count' => ceil($threads->count() / $threadManager->maxPerPage),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all(),
                ],
            ]
        );
    }

    /**
     * Creates a new message thread.
     *
     * @Route("/new-user/{slug}", name="cocorico_message_user_new", requirements={
     *      "id" = "\d+"
     * })
     *
     * @Method({"GET", "POST"})
     *
     * @ParamConverter("user", class="CocoricoUserBundle:User", options={"id" = "slug"})
     *
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws RuntimeException
     */
    public function newThreadUserAction(Request $request, User $user)
    {
        /** @var Form $form */
        $form = $this->get('fos_message.new_thread_form.factory')->create();

        /** @var NewThreadMessage $thread */
        $thread = $form->getData();
        $thread->setUser($user);
        $thread->setSubject($user->getFullName());
        $thread->setRecipient($user);

        return $this->handleMessageForm($form, $thread, $user, $user->getId(), $request->get('_route'));
    }

    /**
     * Creates a new message thread.
     *
     * @Route("/new/{slug}", name="cocorico_dashboard_message_new", requirements={
     *      "id" = "[a-z0-9-]+$"
     * })
     *
     * @Method({"GET", "POST"})
     *
     * @Security("is_granted('view', listing)")
     * @ParamConverter("listing", class="Cocorico\CoreBundle\Entity\Listing", options={"repository_method" = "findOneBySlug"})
     *)
     *
     * @param Request $request
     * @param Listing|null $listing
     * @return RedirectResponse|Response
     * @throws RuntimeException
     */
    public function newThreadAction(Request $request, Listing $listing = null)
    {
        /** @var Form $form */
        $form = $this->get('fos_message.new_thread_form.factory')->create();

        /** @var NewThreadMessage $thread */
        $thread = $form->getData();
        $thread->setListing($listing);
        $thread->setSubject($listing->getTitle());
        $thread->setRecipient($listing->getUser());

        return $this->handleMessageForm($form, $thread, $listing->getUser(), $listing->getSlug(), $request->get('_route'), $listing->getTitle());
    }

    /**
     * @param FormInterface $form
     * @param NewThreadMessage $thread
     * @param User $user
     * @param string $slug
     * @param string $route
     * @param string $title
     * @return Response|null
     */
    protected function handleMessageForm(FormInterface $form, NewThreadMessage $thread, User $user, string $slug, string $route, string $title = '')
    {
        $formHandler = $this->get('fos_message.new_thread_form.handler');

        $form->setData($thread);

        /** @var User $actualUser */
        $actualUser = $this->getUser();

        /** @var Message $message */
        $message = $formHandler->process($form);

        $translator = $this->get('translator');
        $session = $this->get('session');

        if ($message) {
            if ($actualUser->getMemberOrganization()->isMessagesConfirmation()) {
                $message->setVerified(false);
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
            }

            $messageEvent = new MessageEvent($message, $message->getThread(), $user, $actualUser);
            $this->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);
            $this->getRepository()->clearNbUnreadMessageCache($user->getId());

            $session->getFlashBag()->add(
                'success',
                $translator->trans('message.new.success', [], 'cocorico_message')
            );
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('message.new.error', [], 'cocorico_message')
            );
        }

        return $this->render(
            'CocoricoMessageBundle:Dashboard/Message:new_thread.html.twig',
            [
                'form' => $form->createView(),
                'thread' => $form->getData(),
                'user' => $user,
                'slug' => $slug,
                'title' => $title,
                'route' => $route,
            ]
        );
    }

    /**
     * Displays a thread, also allows to reply to it.
     *
     * @Route("/conversation/{threadId}", name="cocorico_dashboard_message_thread_view", requirements={"threadId" = "\d+"})
     *
     * @param Request $request
     * @param int $threadId
     * @return RedirectResponse|Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function threadAction(Request $request, $threadId)
    {
        /** @var User $actualUser */
        $actualUser = $this->getUser();

        /** @var Thread $thread */
        $thread = $this->get('fos_message.provider')->getThread($threadId);
        $this->getRepository()->clearNbUnreadMessageCache($this->getUser()->getId());

        /** @var Form $form */
        $form = $this->get('fos_message.reply_form.factory')->create($thread);
        $paramArr = $request->get($form->getName());
        $request->request->set($form->getName(), $paramArr);

        $formHandler = $this->get('fos_message.reply_form.handler');

        $selfUrl = $this->generateUrl(
            'cocorico_dashboard_message_thread_view',
            ['threadId' => $thread->getId()]
        );

        $message = $formHandler->process($form);
        if ($message) {
            if ($actualUser->getMemberOrganization()->isMessagesConfirmation()) {
                $message->setVerified(false);
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
            }
            $recipients = $thread->getOtherParticipants($this->getUser());
            $recipient = (count($recipients) > 0) ? $recipients[0] : $this->getUser();

            $messageEvent = new MessageEvent($message, $thread, $recipient, $this->getUser());
            $this->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);

            return new RedirectResponse($selfUrl);
        }

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addThreadViewItems($request, $thread, $this->getUser());

        return $this->render(
            'CocoricoMessageBundle:Dashboard/Message:thread.html.twig',
            [
                'form' => $form->createView(),
                'thread' => $thread,
            ]
        );
    }

    /**
     * Deletes a thread
     * Security is managed by FOSMessageProvider
     *
     * @Route("/delete/{threadId}", name="cocorico_dashboard_message_thread_delete", requirements={"threadId" = "\d+"})
     *
     * @param string $threadId the thread id
     *
     * @return RedirectResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function deleteAction($threadId)
    {
        $thread = $this->get('fos_message.provider')->getThread($threadId);
        $this->get('fos_message.deleter')->markAsDeleted($thread);
        $this->get('fos_message.thread_manager')->saveThread($thread);

        $this->getRepository()->clearNbUnreadMessageCache($this->getUser()->getId());

        return new RedirectResponse($this->generateUrl('cocorico_dashboard_message'));
    }

    /**
     * Get number of unread messages for user
     *
     * @Route("/get-nb-unread-messages", name="cocorico_dashboard_message_nb_unread")
     * @Method("GET")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function nbUnReadMessagesAction(Request $request)
    {
        $response = ['total' => 0];
        if ($request->isXmlHttpRequest()) {
            /** @var User $user */
            $user = $this->getUser();
            $nbMessages = $this->getRepository()->getNbUnreadMessage($user);
            $response['total'] = (int) $nbMessages;
        }

        return new JsonResponse($response);
    }

    /**
     * @return MessageRepository|ObjectRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine')->getManager()->getRepository('CocoricoMessageBundle:Message');
    }
}
