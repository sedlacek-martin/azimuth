<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\MessageBuilder;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Doctrine\Common\Collections\Collection;
use FOS\MessageBundle\MessageBuilder\AbstractMessageBuilder;
use FOS\MessageBundle\Model\ParticipantInterface;

/**
 * Fluent interface message builder for new thread messages
 *
 *
 */
class NewThreadMessageBuilder extends AbstractMessageBuilder
{

    /**
     * The thread listing
     *
     * @param Listing|null $listing
     * @return NewThreadMessageBuilder (fluent interface)
     */
    public function setListing(Listing $listing = null)
    {
        $this->thread->setListing($listing);

        return $this;
    }

    /**
     * @param User|null $user
     * @return NewThreadMessageBuilder (fluent interface)
     */
    public function setUser(User $user = null)
    {
        $this->thread->setUser($user);

        return $this;
    }

    /**
     * The thread subject
     *
     * @param  string
     * @return NewThreadMessageBuilder (fluent interface)
     */
    public function setSubject($subject)
    {
        $this->thread->setSubject($subject);

        return $this;
    }

    /**
     * @param  ParticipantInterface $recipient
     * @return NewThreadMessageBuilder (fluent interface)
     */
    public function addRecipient(ParticipantInterface $recipient)
    {
        $this->thread->addParticipant($recipient);

        return $this;
    }

    /**
     * @param  Collection $recipients
     * @return NewThreadMessageBuilder
     */
    public function addRecipients(Collection $recipients)
    {
        foreach ($recipients as $recipient) {
            $this->addRecipient($recipient);
        }

        return $this;
    }

    /**
     * Sets $createdAt message.
     *
     * @param  date
     * @return NewThreadMessageBuilder (fluent interface)
     */
    public function setCreatedAt($createdAt)
    {
        $this->message->setCreatedAt($createdAt);

        return $this;
    }

}
