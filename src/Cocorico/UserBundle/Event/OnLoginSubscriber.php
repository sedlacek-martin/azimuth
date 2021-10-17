<?php

namespace Cocorico\UserBundle\Event;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OnLoginSubscriber implements EventSubscriberInterface
{

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        $onLogin = $request->getSession()->get('loggedIn');


        if ($onLogin !== null) {
            if ($onLogin <= 1) {
                $request->getSession()->set('loggedIn', 2);
            } else {
                $request->getSession()->remove('loggedIn');
            }
        }

    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}