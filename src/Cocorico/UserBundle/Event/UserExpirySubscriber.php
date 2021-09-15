<?php

namespace Cocorico\UserBundle\Event;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserExpirySubscriber implements EventSubscriberInterface
{

    //TODO: add more routes
    public const ALLOWED_ROUTES = [
        'cocorico_user_expired',
        'cocorico_user_dashboard_profile_edit_about_me',
    ];

    /**
     * @var UrlGeneratorInterface
     */
    private $router;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->checkExpiry($event, $user);
    }

    protected function checkExpiry(GetResponseEvent $event, User $user)
    {
        $request = $event->getRequest();

        if ($user->isExpired()) {
            if (!in_array($request->get('_route'), self::ALLOWED_ROUTES)) {
                $event->setResponse(new RedirectResponse($this->router->generate('cocorico_user_expired')));
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}