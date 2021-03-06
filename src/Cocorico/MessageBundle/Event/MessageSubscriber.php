<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Event;

use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Mailer\TwigSwiftMailer;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    protected $mailer;

    public function __construct(TwigSwiftMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param MessageEvent             $event
     * @param                          $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function onMessagePostSend(MessageEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        /** @var Message $message */
        $message = $event->getMessage();
        /** @var User $recipient */
        $recipient = $event->getRecipient();
        $sender = $event->getSender();
        $thread = $event->getThread();

        // only send email notification if message is verified
        if ($message->isVerified() && $recipient->isNewMessageNotifications()) {
            $this->mailer->sendNewThreadMessageToUser($thread->getId(), $recipient, $sender);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageEvents::MESSAGE_POST_SEND => ['onMessagePostSend', 1],
        ];
    }
}
