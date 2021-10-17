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

use FOS\MessageBundle\Model\MessageInterface;
use FOS\MessageBundle\Model\ThreadInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class MessageEvent extends Event
{
    /** @var MessageInterface */
    protected $message;

    /** @var UserInterface */
    protected $recipient;

    /** @var UserInterface */
    protected $sender;

    /** @var ThreadInterface */
    protected $thread;

    /**
     * @param MessageInterface $message
     * @param ThreadInterface $thread
     * @param UserInterface $recipient
     * @param UserInterface $sender
     */
    public function __construct(MessageInterface $message, ThreadInterface $thread, UserInterface $recipient, UserInterface $sender)
    {
        $this->message = $message;
        $this->thread = $thread;
        $this->recipient = $recipient;
        $this->sender = $sender;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param MessageInterface $message
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @return UserInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return UserInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return ThreadInterface
     */
    public function getThread(): ThreadInterface
    {
        return $this->thread;
    }

    /**
     * @param ThreadInterface $thread
     */
    public function setThread(ThreadInterface $thread): void
    {
        $this->thread = $thread;
    }


}
