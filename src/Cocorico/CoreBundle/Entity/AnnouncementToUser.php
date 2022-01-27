<?php

namespace Cocorico\CoreBundle\Entity;

use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\AnnouncementToUserRepository")
 * @ORM\Table(name="announcement_to_user", uniqueConstraints={
 *      @UniqueConstraint(name="announcement_user", columns={"announcement_id", "user_id"})
 * })
 */
class AnnouncementToUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Announcement", inversedBy="users")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var Announcement|null
     */
    public $announcement;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="announcements")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var User|null
     */
    public $user;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    public $dismissed = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    public $dismissedAt;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    public $displayed = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    public $displayedAt;

    public function __construct(Announcement $announcement, User $user)
    {
        $this->user = $user;
        $this->announcement = $announcement;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AnnouncementToUser
     */
    public function setId(int $id): AnnouncementToUser
    {
        $this->id = $id;

        return $this;
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
     * @return AnnouncementToUser
     */
    public function setUser(?User $user): AnnouncementToUser
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDismissed(): bool
    {
        return $this->dismissed;
    }

    /**
     * @param bool $dismissed
     * @return AnnouncementToUser
     */
    public function setDismissed(bool $dismissed): AnnouncementToUser
    {
        $this->dismissed = $dismissed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return $this->displayed;
    }

    /**
     * @param bool $displayed
     * @return AnnouncementToUser
     */
    public function setDisplayed(bool $displayed): AnnouncementToUser
    {
        $this->displayed = $displayed;

        return $this;
    }

    /**
     * @return Announcement|null
     */
    public function getAnnouncement(): ?Announcement
    {
        return $this->announcement;
    }

    /**
     * @param Announcement|null $announcement
     * @return AnnouncementToUser
     */
    public function setAnnouncement(?Announcement $announcement): AnnouncementToUser
    {
        $this->announcement = $announcement;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDismissedAt(): ?\DateTime
    {
        return $this->dismissedAt;
    }

    /**
     * @param \DateTime|null $dismissedAt
     * @return AnnouncementToUser
     */
    public function setDismissedAt(?\DateTime $dismissedAt): AnnouncementToUser
    {
        $this->dismissedAt = $dismissedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDisplayedAt(): ?\DateTime
    {
        return $this->displayedAt;
    }

    /**
     * @param \DateTime|null $displayedAt
     * @return AnnouncementToUser
     */
    public function setDisplayedAt(?\DateTime $displayedAt): AnnouncementToUser
    {
        $this->displayedAt = $displayedAt;

        return $this;
    }

    public function __toString()
    {
        return "Announcement {$this->getAnnouncement()->getHeading()} to user {$this->getUser()->getFullName()}";
    }
}
