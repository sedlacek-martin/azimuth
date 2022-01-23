<?php

namespace Cocorico\CoreBundle\Entity;

use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Page Access
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\PageAccessRepository")
 *
 * @ORM\Table(name="page_access")
 */
class PageAccess
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
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=100, nullable=false)
     */
    protected $route;

    /**
     * @var string|null
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=true)
     */
    protected $slug;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User|null
     */
    protected $user;

    /**
     * @ORM\Column(name="accessed_at", type="datetime")
     *
     * @var \DateTime
     */
    protected $accessedAt;

    /**
     * @param string $route
     * @param User|null $user
     * @return PageAccess
     */
    public static function create(string $route, ?User $user, string $slug = null): PageAccess
    {
        return (new self())
            ->setRoute($route)
            ->setSlug($slug)
            ->setUser($user);
    }

    public function __construct()
    {
        $this->accessedAt = new \DateTime();
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
     * @return PageAccess
     */
    public function setId(int $id): PageAccess
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return PageAccess
     */
    public function setRoute(string $route): PageAccess
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     * @return PageAccess
     */
    public function setSlug(?string $slug): PageAccess
    {
        $this->slug = $slug;

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
     * @return PageAccess
     */
    public function setUser(?User $user): PageAccess
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAccessedAt(): \DateTime
    {
        return $this->accessedAt;
    }

    /**
     * @param \DateTime $accessedAt
     * @return PageAccess
     */
    public function setAccessedAt(\DateTime $accessedAt): PageAccess
    {
        $this->accessedAt = $accessedAt;

        return $this;
    }
}
