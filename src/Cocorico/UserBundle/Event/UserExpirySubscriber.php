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
    public const ALLOWED_ROUTES = [
        'cocorico_user_expired',
        'cocorico_user_dashboard_profile_edit_about_me',
        'cocorico_user_reconfirm',
        'cocorico_user_dashboard_profile_edit_scout_info',
        'cocorico_user_dashboard_profile_edit_contact',
        'cocorico_user_profile_show',
        'cocorico_user_dashboard_profile_delete',
    ];

    public const ALLOWED_ROLES = [
        'ROLE_DEVELOPER',
        'ROLE_SUPER_ADMIN',
    ];

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var TokenStorageInterface */
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

        $intersectedRoles = array_intersect($user->getRoles(), self::ALLOWED_ROLES);
        if (count($intersectedRoles) > 0) {
            return;
        }

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
