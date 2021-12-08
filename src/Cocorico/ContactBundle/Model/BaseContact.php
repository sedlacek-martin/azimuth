<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Model;

use Cocorico\ContactBundle\Entity\ContactCategory;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\TranslationBundle\Model\Message;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseContact
 *
 * @ORM\MappedSuperclass()
 *
 */
abstract class BaseContact
{
    /* Status */
    const STATUS_NEW = 1;
    const STATUS_READ = 2;

    public static $statusValues = array(
        self::STATUS_NEW => 'entity.contact.status.new',
        self::STATUS_READ => 'entity.contact.status.read',
    );

    public const RECIPIENT_ROLES = [
        'ROLE_SUPER_ADMIN' => 'role.super_admin',
        'ROLE_FACILITATOR' => 'role.facilitator',
        'ROLE_ACTIVATOR' => 'role.activator',
    ];

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(message="cocorico_contact.first_name.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_contact.first_name.short",
     *     maxMessage="cocorico_contact.first_name.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(message="cocorico_contact.last_name.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_contact.last_name.short",
     *     maxMessage="cocorico_contact.last_name.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=1024)
     *
     * @Assert\Email(message="cocorico_contact.email.invalid", groups={"CocoricoContact"})
     *
     * @Assert\NotBlank(message="cocorico_contact.email.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="1024",
     *     minMessage="cocorico_contact.email.short",
     *     maxMessage="cocorico_contact.email.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=1024)
     *
     * @Assert\NotBlank(message="cocorico_contact.subject.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="1024",
     *     minMessage="cocorico_contact.subject.short",
     *     maxMessage="cocorico_contact.subject.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     *
     * @Assert\NotBlank(message="cocorico_contact.message.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     minMessage="cocorico_contact.message.short",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $message;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reply", type="text", nullable=true)
     *
     * @Assert\Length(
     *     min=3,
     *     minMessage="cocorico_contact.message.short",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $reply;

    /**
     * @var string[]
     *
     * @ORM\Column(name="recipient_roles", type="simple_array", nullable=true)
     */
    protected $recipientRoles;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User|null
     */
    protected $user;


    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\ContactBundle\Entity\ContactCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank(message="cocorico_contact.category.blank", groups={"CocoricoContact"})
     *
     * @var ContactCategory|null
     */
    protected $category;


    /**
     * @ORM\Column(name="status", type="smallint")
     *
     * @var integer
     */
    protected $status = self::STATUS_NEW;


    /**
     * @var bool
     *
     * @ORM\Column(name="reply_send", type="boolean", nullable=false)
     */
    protected $replySend = false;

    /**
     * Gets First Name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }


    /**
     * Sets FirstName
     *
     * @param string $firstName the first name
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Gets Last Name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets Last Name
     *
     * @param string $lastName the last name
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Gets Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets Email
     *
     * @param string $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets Subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets Subject
     *
     * @param string $subject the subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Gets Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets Subject
     *
     * @param string $message the message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param integer $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for contact.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;

    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusValues[$this->getStatus()];
    }

    /**
     * @return string|null
     */
    public function getReply (): ?string
    {
        return $this->reply;
    }

    /**
     * @param string|null $reply
     * @return BaseContact
     */
    public function setReply (?string $reply): BaseContact
    {
        $this->reply = $reply;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRecipientRoles(): array
    {
        return $this->recipientRoles;
    }


    /**
     * @param string[] $recipientRoles
     * @return BaseContact
     */
    public function setRecipientRoles(array $recipientRoles): BaseContact
    {
        $this->recipientRoles = $recipientRoles;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecipientRoleNames(): array
    {
        return array_map(function ($val) {
            return ContactCategory::getRecipientRoleName($val);
        }, $this->recipientRoles);

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
     * @return BaseContact
     */
    public function setUser(?User $user): BaseContact
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return ContactCategory|null
     */
    public function getCategory(): ?ContactCategory
    {
        return $this->category;
    }

    /**
     * @param ContactCategory|null $category
     * @return BaseContact
     */
    public function setCategory(?ContactCategory $category): BaseContact
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReplySend (): bool
    {
        return $this->replySend;
    }

    /**
     * @param bool $replySend
     * @return BaseContact
     */
    public function setReplySend (bool $replySend): BaseContact
    {
        $this->replySend = $replySend;
        return $this;
    }
}
