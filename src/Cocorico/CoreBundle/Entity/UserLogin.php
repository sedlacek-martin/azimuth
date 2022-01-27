<?php

namespace Cocorico\CoreBundle\Entity;

use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserLogin
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\UserLoginRepository")
 *
 * @ORM\Table(name="user_login")
 */
class UserLogin
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
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User|null
     */
    protected $user;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ip", type="string", length=60, nullable=true)
     */
    protected $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @param User $user
     * @return UserLogin
     */
    public static function create(User $user, string $ip = null): UserLogin
    {
        return (new self())
            ->setUser($user)
            ->setIp($ip);
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * @return UserLogin
     */
    public function setId(int $id): UserLogin
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
     * @return UserLogin
     */
    public function setUser(?User $user): UserLogin
    {
        $this->user = $user;

        return $this;
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
     * @return UserLogin
     */
    public function setCreatedAt(\DateTime $createdAt): UserLogin
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     * @return UserLogin
     */
    public function setIp(?string $ip): UserLogin
    {
        $this->ip = $ip;

        return $this;
    }
}
