<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Entity;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Entity\Thread as BaseThread;

/**
 * @ORM\Entity(repositoryClass="Cocorico\MessageBundle\Repository\ThreadRepository")
 *
 * @ORM\Table(name="message_thread")
 */
class Thread extends BaseThread
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $createdBy;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Cocorico\MessageBundle\Entity\Message",
     *   mappedBy="thread"
     * )
     * @var Message[]|\Doctrine\Common\Collections\Collection
     */
    protected $messages;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Cocorico\MessageBundle\Entity\ThreadMetadata",
     *   mappedBy="thread",
     *   cascade={"all"}
     * )
     * @var ThreadMetadata[]|\Doctrine\Common\Collections\Collection
     */
    protected $metadata;

    /**
     * @var Listing|null
     *
     * @ORM\ManyToOne(targetEntity="\Cocorico\CoreBundle\Entity\Listing", inversedBy="threads")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $listing;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="\Cocorico\UserBundle\Entity\User", inversedBy="threads")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="from_date", type="date", nullable=true)
     */
    protected $fromDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="to_date", type="date", nullable=true)
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
    public function getSubject(): string
    {
        if ($this->getListing()) {
            return $this->getListing()->getTitle();
        }

        return $this->getUser()->getName();
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
     */
    public function setFromDate(?\DateTime $fromDate): void
    {
        $this->fromDate = $fromDate;
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
     */
    public function setToDate(?\DateTime $toDate): void
    {
        $this->toDate = $toDate;
    }

    public function __toString()
    {
        return "" . $this->getId();
    }

    /**
     * @param Message $message
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }


}
