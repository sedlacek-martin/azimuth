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

use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Entity\Message as BaseMessage;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\MessageBundle\Model\ThreadInterface;

/**
 * @ORM\Entity(repositoryClass="Cocorico\MessageBundle\Repository\MessageRepository")
 *
 * @ORM\Table(name="message")
 */
class Message extends BaseMessage
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Cocorico\MessageBundle\Entity\Thread",
     *   inversedBy="messages"
     * )
     * @var ThreadInterface
     */
    protected $thread;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Cocorico\UserBundle\Entity\User",
     *   inversedBy="messages")
     * @var ParticipantInterface
     */
    protected $sender;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Cocorico\MessageBundle\Entity\MessageMetadata",
     *   mappedBy="message",
     *   cascade={"all"}
     * )
     * @var MessageMetadata
     */
    protected $metadata;

    /**
     * @var bool
     *
     * @ORM\Column(name="verified", type="boolean", nullable=false)
     */
    protected $verified = true;

    /**
     * @ORM\Column(name="admin_note", type="text", length=65535, nullable=true)
     *
     * @var string|null
     */
    protected $adminNote;

    /**
     * @ORM\Column(name="verified_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $verifiedAt;

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * @param bool $verified
     */
    public function setVerified(bool $verified): void
    {
        $this->verified = $verified;
        if ($verified) {
            $this->verifiedAt = new \DateTime();
        }
    }

    /**
     * @return string|null
     */
    public function getAdminNote(): ?string
    {
        return $this->adminNote;
    }

    /**
     * @param string|null $adminNote
     */
    public function setAdminNote(?string $adminNote): void
    {
        $this->adminNote = $adminNote;
    }

    public function setCreatedAt($createdAt)
    {
        return $this->createdAt = $createdAt;
    }
}
