<?php

namespace Cocorico\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ContactCategory
 *
 * @ORM\Entity(repositoryClass="Cocorico\ContactBundle\Repository\ContactCategoryRepository")
 *
 * @ORM\Table(name="contact_category")
 */
class ContactCategory
{
    public static $recipientRolesValues = [
        'ROLE_SUPER_ADMIN' => 'role.super_admin',
        'ROLE_FACILITATOR' => 'role.facilitator',
        'ROLE_ACTIVATOR' => 'role.activator',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="uri", type="string", nullable=false)
     */
    protected $uri;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="subject", type="string", nullable=true)
     */
    protected $subject;

    /**
     * @var string[]
     *
     * @ORM\Column(name="recipient_roles", type="simple_array", nullable=true)
     * @Assert\NotBlank(message="cocorico_contact.recipient_roles.blank", groups={"CocoricoContact"})
     */
    protected $recipientRoles;

    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean", nullable=false)
     */
    protected $public = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_subject", type="boolean", nullable=false)
     */
    protected $allowSubject = false;

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     * @return ContactCategory
     */
    public function setUri(?string $uri): ContactCategory
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return ContactCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecipientRoles(): ?array
    {
        return $this->recipientRoles;
    }

    /**
     * @param array $recipientRoles
     * @return ContactCategory
     */
    public function setRecipientRoles(array $recipientRoles): ContactCategory
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
            return self::getRecipientRoleName($val);
        }, $this->recipientRoles);
    }

    public static function getRecipientRoleName(string $role): string
    {
        return self::$recipientRolesValues[$role] ?? '';
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     * @return ContactCategory
     */
    public function setPublic(bool $public): ContactCategory
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowSubject(): bool
    {
        return $this->allowSubject;
    }

    /**
     * @param bool $allowSubject
     * @return ContactCategory
     */
    public function setAllowSubject(bool $allowSubject): ContactCategory
    {
        $this->allowSubject = $allowSubject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        if (empty($this->subject)) {
            return $this->name;
        }

        return $this->subject;
    }

    /**
     * @param string|null $subject
     * @return ContactCategory
     */
    public function setSubject(?string $subject): ContactCategory
    {
        $this->subject = $subject;

        return $this;
    }

    public function __toString()
    {
        return $this->getName() ?? '';
    }
}
