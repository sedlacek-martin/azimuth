<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\FormModel;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use FOS\MessageBundle\FormModel\AbstractMessage;
use FOS\MessageBundle\Model\ParticipantInterface;

class NewThreadMessage extends AbstractMessage
{

    /**
     * The user who receives the message
     *
     * @var ParticipantInterface
     */
    protected $recipient;

    /**
     * The thread subject
     *
     * @var string
     */
    protected $subject;

    /**
     * The thread listing
     *
     * @var Listing|null
     */
    protected $listing;

    /**
     * The thread user
     *
     * @var User|null
     */
    protected $user;

    /**
     * @var \DateTime|null
     */
    protected $fromDate;

    /**
     * @var \DateTime|null
     */
    protected $toDate;

    /**
     * @return Listing|null
     */
    public function getListing(): ?Listing
    {
        return $this->listing;
    }

    /**
     * @param Listing|null $listing
     */
    public function setListing(?Listing $listing): void
    {
        $this->listing = $listing;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param  string
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return ParticipantInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param  ParticipantInterface
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getFromDate(): ?\DateTime
    {
        return $this->fromDate;
    }

    /**
     * @param \DateTime|null $fromDate
     * @return NewThreadMessage
     */
    public function setFromDate(?\DateTime $fromDate): NewThreadMessage
    {
        $this->fromDate = $fromDate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getToDate(): ?\DateTime
    {
        return $this->toDate;
    }

    /**
     * @param \DateTime|null $toDate
     * @return NewThreadMessage
     */
    public function setToDate(?\DateTime $toDate): NewThreadMessage
    {
        $this->toDate = $toDate;
        return $this;
    }
}
