<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Announcement
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\AnnouncementRepository")
 *
 * @ORM\Table(name="announcement")
 */
class Announcement
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
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    public $heading = '';

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    public $shortDescription = '';

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    public $content = '';

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\AnnouncementToUser", mappedBy="announcement", cascade={"persist", "remove"})
     * @var AnnouncementToUser[]|Collection
     */
    public $users;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="show_at", type="datetime")
     */
    protected $showAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->users = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Announcement
     */
    public function setId(int $id): Announcement
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeading(): string
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     * @return Announcement
     */
    public function setHeading(string $heading): Announcement
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     * @return Announcement
     */
    public function setShortDescription(string $shortDescription): Announcement
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Announcement
     */
    public function setContent(string $content): Announcement
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return AnnouncementToUser[]|Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param AnnouncementToUser[]|Collection $users
     * @return Announcement
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return int
     */
    public function getUsersCount(): int
    {
//        return 1;
        return $this->users->count();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Announcement
     */
    public function setCreatedAt(\DateTime $createdAt): Announcement
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getShowAt(): ?\DateTime
    {
        return $this->showAt;
    }

    /**
     * @param \DateTime|null $showAt
     * @return Announcement
     */
    public function setShowAt(?\DateTime $showAt): Announcement
    {
        $this->showAt = $showAt;

        return $this;
    }

    public function __toString()
    {
        return "Announcement - {$this->getHeading()}";
    }
}
