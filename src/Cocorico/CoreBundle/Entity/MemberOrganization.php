<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Intl;

/**
 * MemberOrganization
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\MemberOrganizationRepository")
 *
 * @ORM\Table(name="member_organization")
 */
class MemberOrganization
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
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=3, nullable=true)
     */
    protected $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     * TODO: Make this translatable
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * TODO: Make this translatable
     * @var string|null
     *
     * @ORM\Column(name="abstract", type="string", length=255, nullable=true)
     */
    protected $abstract;

    /**
     * @var bool
     *
     * @ORM\Column(name="posts_validation", type="boolean", nullable=false)
     */
    protected $postsValidation = false;

    /**
     * @var string|null
     *
     *  @ORM\Column(name="user_identifier_description", type="string", length=255, nullable=true)
     */
    protected $userIdentifierDescription;

    /**
     * @var bool
     *
     * @ORM\Column(name="requires_user_identifier", type="boolean", nullable=false)
     */
    protected $requiresUserIdentifier = false;

    /**
     * @var int
     *
     * @ORM\Column(name="user_expiry_period", type="integer", nullable=false)
     */
    protected $userExpiryPeriod = 365;

    /**
     * @var bool
     *
     * @ORM\Column(name="registration_accept_domain", type="boolean", nullable=false)
     */
    protected $registrationAcceptDomain = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="registration_accept_activation", type="boolean", nullable=false)
     */
    protected $registrationAcceptActivation = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="post_confirmation", type="boolean", nullable=false)
     */
    protected $postConfirmation = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="messages_confirmation", type="boolean", nullable=false)
     */
    protected $messagesConfirmation = false;

    public function __construct()
    {
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
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCountryName(): string
    {
        $countries = Intl::getRegionBundle()->getCountryNames();

        return $countries[$this->country] ?? '';
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    /**
     * @param string|null $abstract
     */
    public function setAbstract(?string $abstract): void
    {
        $this->abstract = $abstract;
    }

    /**
     * @return bool
     */
    public function isPostsValidation(): bool
    {
        return $this->postsValidation;
    }

    /**
     * @param bool $postsValidation
     */
    public function setPostsValidation(bool $postsValidation): void
    {
        $this->postsValidation = $postsValidation;
    }

    /**
     * @return string|null
     */
    public function getUserIdentifierDescription(): ?string
    {
        return $this->userIdentifierDescription;
    }

    /**
     * @param string|null $userIdentifierDescription
     */
    public function setUserIdentifierDescription(?string $userIdentifierDescription): void
    {
        $this->userIdentifierDescription = $userIdentifierDescription;
    }

    /**
     * @return bool
     */
    public function isRequiresUserIdentifier(): bool
    {
        return $this->requiresUserIdentifier;
    }

    /**
     * @param bool $requiresUserIdentifier
     */
    public function setRequiresUserIdentifier(bool $requiresUserIdentifier): void
    {
        $this->requiresUserIdentifier = $requiresUserIdentifier;
    }

    /**
     * @return int
     */
    public function getUserExpiryPeriod(): int
    {
        return $this->userExpiryPeriod;
    }

    /**
     * @param int $userExpiryPeriod
     */
    public function setUserExpiryPeriod(int $userExpiryPeriod): void
    {
        $this->userExpiryPeriod = $userExpiryPeriod;
    }

    /**
     * @return bool
     */
    public function isRegistrationAcceptDomain(): bool
    {
        return $this->registrationAcceptDomain;
    }

    /**
     * @param bool $registrationAcceptDomain
     */
    public function setRegistrationAcceptDomain(bool $registrationAcceptDomain): void
    {
        $this->registrationAcceptDomain = $registrationAcceptDomain;
    }

    /**
     * @return bool
     */
    public function isRegistrationAcceptActivation(): bool
    {
        return $this->registrationAcceptActivation;
    }

    /**
     * @param bool $registrationAcceptActivation
     */
    public function setRegistrationAcceptActivation(bool $registrationAcceptActivation): void
    {
        $this->registrationAcceptActivation = $registrationAcceptActivation;
    }

    /**
     * @return bool
     */
    public function isPostConfirmation(): bool
    {
        return $this->postConfirmation;
    }

    /**
     * @param bool $postConfirmation
     */
    public function setPostConfirmation(bool $postConfirmation): void
    {
        $this->postConfirmation = $postConfirmation;
    }

    /**
     * @return bool
     */
    public function isMessagesConfirmation(): bool
    {
        return $this->messagesConfirmation;
    }

    /**
     * @param bool $messagesConfirmation
     */
    public function setMessagesConfirmation(bool $messagesConfirmation): void
    {
        $this->messagesConfirmation = $messagesConfirmation;
    }

    public function __toString()
    {
        return $this->getName() ?? '';
    }
}
