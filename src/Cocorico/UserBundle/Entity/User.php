<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Entity;

use Cocorico\CoreBundle\Entity\AnnouncementToUser;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\UserBundle\Validator\Constraints as CocoricoUserAssert;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\UserBundle\Model\User as BaseUser;
use InvalidArgumentException;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User.
 *
 * @CocoricoUserAssert\User()
 *
 * @ORM\Entity(repositoryClass="Cocorico\UserBundle\Repository\UserRepository")
 *
 * @UniqueEntity(
 *      fields={"email"},
 *      groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @UniqueEntity(
 *      fields={"username"},
 *      groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @ORM\Table(name="`user`",indexes={
 *    @ORM\Index(name="created_at_u_idx", columns={"createdAt"}),
 *    @ORM\Index(name="slug_u_idx", columns={"slug"}),
 *    @ORM\Index(name="enabled_idx", columns={"enabled"}),
 *    @ORM\Index(name="email_idx", columns={"email"})
 *  })
 */
class User extends BaseUser implements ParticipantInterface
{
    const ADMIN_ROLES = [
        'ROLE_ADMIN',
        'ROLE_FACILITATOR',
        'ROLE_ACTIVATOR',
        'ROLE_SUPER_ADMIN',
        'ROLE_DEVELOPER',
    ];

    const MALE = 'male';
    const FEMALE = 'female';
    const OTHER = 'other';

    use ORMBehaviors\Timestampable\Timestampable;
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\Sluggable\Sluggable;

    /**
     * Fix missing validation on translations fields
     * @Assert\Valid
     */
    protected $translations;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\Email(message="cocorico_user.email.invalid", strict=true, groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"})
     *
     * @Assert\NotBlank(message="cocorico_user.email.blank", groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_user.username.short",
     *     maxMessage="cocorico_user.username.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"}
     * )
     */
    protected $email;

    /**
     * @ORM\Column(name="last_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.last_name.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"
     * })
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.last_name.short",
     *     maxMessage="cocorico_user.last_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"}
     * )
     */
    protected $lastName;

    /**
     * @ORM\Column(name="first_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.first_name.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"
     * })
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.first_name.short",
     *     maxMessage="cocorico_user.first_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"}
     * )
     */
    protected $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="scout_name", type="string", length=100, nullable=true)
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.scout_name.short",
     *     maxMessage="cocorico_user.scout_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"}
     * )
     */
    protected $scoutName;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="birthday", type="date")
     *
     * @Assert\NotBlank(message="cocorico_user.birthday.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfileBankAccount"
     * })
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=3, nullable=true)
     */
    protected $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="location", type="string", length=100, nullable=true)
     */
    protected $location;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gender", type="string", length=10, nullable=true)
     */
    protected $gender;

    /**
     * @Assert\Length(
     *      min = 6,
     *      minMessage = "{{ limit }}cocorico_user.password.short",
     * )
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @ORM\Column(name="email_verified", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $emailVerified = false;

    /**
     * @ORM\Column(name="trusted", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $trusted = false;

    /**
     * @ORM\Column(name="trusted_email_sent", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $trustedEmailSent = false;

    /**
     * @ORM\Column(name="mother_tongue", type="string", length=5, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.motherTongue.blank", groups={"CocoricoProfile"})
     *
     * @var string
     */
    protected $motherTongue;

    /**
     * @ORM\Column(name="time_zone", type="string", length=100,  nullable=false)
     *
     * @var string
     */
    protected $timeZone = 'UTC';

    /** @ORM\OneToMany(targetEntity="Cocorico\MessageBundle\Entity\Message", mappedBy="sender", cascade={"remove"}, orphanRemoval=true) */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\Listing", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var Listing[]
     */
    private $listings;

    /**
     * For Asserts : @see \Cocorico\UserBundle\Validator\Constraints\UserValidator.
     *
     * @ORM\OneToMany(targetEntity="UserImage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     *
     * @var UserImage[]
     */
    protected $images;

    /**
     * @ORM\OneToMany(targetEntity="UserLanguage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var UserLanguage[]
     */
    protected $languages;

    /**
     * @var string|null
     *
     * @ORM\Column(name="organizationIdentifier", type="string", length=50, nullable=true)
     */
    protected $organizationIdentifier;

    /**
     * @var MemberOrganization
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\MemberOrganization")
     */
    protected $memberOrganization;

    /**
     * @ORM\Column(name="verified_domain_registration", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $verifiedDomainRegistration = false;

    /**
     * @ORM\Column(name="scout_since", type="integer", nullable=true)
     *
     * @var int|null
     */
    protected $scoutSince;

    /**
     * @var string
     *
     * @ORM\Column(name="unique_hash", type="string", length=25)
     */
    protected $uniqueHash;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\MessageBundle\Entity\Thread", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "desc"})
     */
    private $threads;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiry_date", type="date")
     */
    protected $expiryDate;

    /**
     * @ORM\Column(name="expiry_notification_send", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $expiryNotificationSend = false;

    /**
     * @ORM\Column(name="reconfirm_requested", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $reconfirmRequested = false;

    /**
     * @ORM\Column(name="reconfirm_requested_at", type="datetime", nullable=true)
     * @var DateTime|null
     */
    protected $reconfirmRequestedAt;

    /**
     * @ORM\Column(name="new_message_notifications", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $newMessageNotifications = true;

    /**
     * @ORM\Column(name="verified_at", type="datetime", nullable=true)
     * @var DateTime
     */
    protected $verifiedAt;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\AnnouncementToUser", mappedBy="user")
     * @var AnnouncementToUser[]|Collection
     */
    protected $announcements;

    /**
     * @ORM\Column(name="disable_admin_notifications", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $disableAdminNotifications = false;

    /**
     * @ORM\Column(name="expired_send", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $expiredSend = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->listings = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->announcements = new ArrayCollection();

        // set expiry date to one year from now - you can change it later if needed
        $this->expiryDate = (new DateTime())->add(new DateInterval('P1Y'));
        $this->uniqueHash = str_replace('.', 'd', uniqid('', true));
        parent::__construct();
    }

    public function getSluggableFields()
    {
        return ['firstName', 'id'];
    }

    /**
     * Translation proxy.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->firstName . ' ' . ucfirst(substr($this->lastName, 0, 1) . '.');
    }

    /**
     * @return string|null
     */
    public function getScoutName(): ?string
    {
        return $this->scoutName;
    }

    /**
     * @param string|null $scoutName
     */
    public function setScoutName(?string $scoutName): void
    {
        $this->scoutName = $scoutName;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }

    /**
     * Set emailVerified.
     *
     * @param bool $emailVerified
     *
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * Get emailVerified.
     *
     * @return bool
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    public function getAge()
    {
        $now = new DateTime('now');

        return $now->diff($this->getBirthday())->y;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     */
    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getMotherTongue()
    {
        return $this->motherTongue;
    }

    /**
     * @param string $motherTongue
     */
    public function setMotherTongue($motherTongue)
    {
        $this->motherTongue = $motherTongue;
    }

    public function getFullName()
    {
        return implode(' ', array_filter([$this->getFirstName(), $this->getLastName()]));
    }

    /**
     * Add listings.
     *
     * @param Listing $listing
     *
     * @return User
     */
    public function addListing(Listing $listing)
    {
        $this->listings[] = $listing;

        return $this;
    }

    /**
     * Remove listings.
     *
     * @param Listing $listing
     */
    public function removeListing(Listing $listing)
    {
        $this->listings->removeElement($listing);
    }

    /**
     * Get listings.
     *
     * @return Listing[]|ArrayCollection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Add images.
     *
     * @param UserImage $image
     *
     * @return $this
     */
    public function addImage(UserImage $image)
    {
        $image->setUser($this); //Because the owning side of this relation is user image
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove images.
     *
     * @param UserImage $image
     */
    public function removeImage(UserImage $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images.
     *
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add language.
     *
     * @param UserLanguage $language
     *
     * @return $this
     */
    public function addLanguage(UserLanguage $language)
    {
        $language->setUser($this);
        $this->languages[] = $language;

        return $this;
    }

    /**
     * Remove language.
     *
     * @param UserLanguage $language
     */
    public function removeLanguage(UserLanguage $language)
    {
        $this->languages->removeElement($language);
    }

    /**
     * Get languages.
     *
     * @return ArrayCollection|UserLanguage[]
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return bool
     */
    public function hasLanguage(): bool
    {
        foreach ($this->languages as $language) {
            return true;
        }

        return false;
    }

    /**
     * @return ArrayCollection|Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ArrayCollection|Message[] $messages
     */
    public function setMessages(ArrayCollection $messages)
    {
        foreach ($messages as $message) {
            $message->setSender($this);
        }

        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * @param int $minImages
     * @param $minDescriptionLength
     * @return array
     */
    public function getCompletionInformation($minImages, $minDescriptionLength): array
    {
        return [
            'description' => ($this->getDescription() && strlen($this->getDescription()) > $minDescriptionLength) ? 1 : 0,
            'image' => (count($this->getImages()) >= $minImages) ? 1 : 0,
            'location' => $this->getLocation() !== null ? 1 : 0,
            'languages' => $this->hasLanguage() ? 1 : 0,
        ];
    }

    /**
     * @param $minImages
     * @param $minDescriptionLength
     * @return bool
     */
    public function hasCompleteInformation($minImages, $minDescriptionLength): bool
    {
        foreach ($this->getCompletionInformation($minImages, $minDescriptionLength) as $complete) {
            if (!$complete) {
                return false;
            }
        }

        return true;
    }

    /**
     * Guess preferred site language from motherTongue, and spoken languages and  sites locales enabled.
     *
     * todo: Add "preferred language" field to user entity and set it by default to mother tongue while registration, add it to editable fields and add it to the checked fields of this method.
     *
     * @param array  $siteLocales
     * @param string $defaultLocale
     *
     * @return string
     */
    public function guessPreferredLanguage($siteLocales, $defaultLocale)
    {
        if ($this->getMotherTongue() && in_array($this->getMotherTongue(), $siteLocales)) {
            return $this->getMotherTongue();
        } elseif ($this->getLanguages()->count()) {
            foreach ($this->getLanguages() as $language) {
                if (in_array($language->getCode(), $siteLocales)) {
                    return $language->getCode();
                }
            }
        }

        return $defaultLocale;
    }

    /**
     * To add impersonating link into admin :
     *
     * @return User
     */
    public function getImpersonating()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isTrusted(): bool
    {
        return $this->trusted;
    }

    /**
     * @param bool $trusted
     * @return User
     */
    public function setTrusted(bool $trusted): User
    {
        $this->trusted = $trusted;

        if ($trusted) {
            $this->verifiedAt = new DateTime();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isTrustedEmailSent(): bool
    {
        return $this->trustedEmailSent;
    }

    /**
     * @param bool $trustedEmailSent
     * @return User
     */
    public function setTrustedEmailSent(bool $trustedEmailSent): User
    {
        $this->trustedEmailSent = $trustedEmailSent;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailDomain(): string
    {
        if (($email = filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) !== false) {
            return substr($email, strrpos($email, '@') + 1);
        }

        throw new InvalidArgumentException('Invalid email format');
    }

    public static function flattenRoles($rolesHierarchy): array
    {
        $flatRoles = [];
        foreach ($rolesHierarchy as $key => $roles) {
            if (!isset($flatRoles[$key])) {
                $flatRoles[$key] = $key;
            }

            if (empty($roles)) {
                continue;
            }

            foreach ($roles as $role) {
                if (!isset($flatRoles[$role])) {
                    $flatRoles[$role] = $role;
                }
            }
        }

        return $flatRoles;
    }

    /**
     * @return string|null
     */
    public function getOrganizationIdentifier(): ?string
    {
        return $this->organizationIdentifier;
    }

    /**
     * @param string $organizationIdentifier
     */
    public function setOrganizationIdentifier(string $organizationIdentifier): void
    {
        $this->organizationIdentifier = $organizationIdentifier;
    }

    /**
     * @return MemberOrganization|null
     */
    public function getMemberOrganization(): ?MemberOrganization
    {
        return $this->memberOrganization;
    }

    /**
     * @param MemberOrganization $memberOrganization
     */
    public function setMemberOrganization(MemberOrganization $memberOrganization): void
    {
        $this->memberOrganization = $memberOrganization;
    }

    /**
     * @return bool
     */
    public function isVerifiedDomainRegistration(): bool
    {
        return $this->verifiedDomainRegistration;
    }

    /**
     * @param bool $verifiedDomainRegistration
     */
    public function setVerifiedDomainRegistration(bool $verifiedDomainRegistration): void
    {
        $this->verifiedDomainRegistration = $verifiedDomainRegistration;
    }

    /**
     * @return int|null
     */
    public function getScoutSince(): ?int
    {
        return $this->scoutSince;
    }

    /**
     * @param int|null $scoutSince
     */
    public function setScoutSince(?int $scoutSince): void
    {
        $this->scoutSince = $scoutSince;
    }

    /**
     * @return Thread
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * @param Thread[] $threads
     */
    public function setThreads($threads)
    {
        foreach ($threads as $thread) {
            $thread->setUser($this);
        }
        $this->threads = $threads;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpiryDate(): DateTime
    {
        return $this->expiryDate;
    }

    /**
     * @param DateTime $expiryDate
     */
    public function setExpiryDate(DateTime $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->getExpiryDate() < new \DateTime('now');
    }

    /**
     * @return bool
     */
    public function isExpiredSend(): ?bool
    {
        return $this->expiredSend;
    }

    /**
     * @param bool $expiredSend
     * @return User
     */
    public function setExpiredSend(bool $expiredSend): User
    {
        $this->expiredSend = $expiredSend;

        return $this;
    }

    /**
     * @param int $days
     * @return bool
     */
    public function isExpiredSoon(int $days = 30): bool
    {
        return $this->getExpiryDate() < (new DateTime('now'))->add(new DateInterval("P{$days}D"));
    }

    /**
     * @param int $days
     * @return bool
     */
    public function isToBeDeleted(int $days = 30): bool
    {
        return $this->getExpiryDate()->add(new DateInterval("P{$days}D")) < (new DateTime('now'));
    }

    /**
     * @return bool
     */
    public function isExpiryNotificationSend(): bool
    {
        return $this->expiryNotificationSend;
    }

    /**
     * @param bool $expiryNotificationSend
     */
    public function setExpiryNotificationSend(bool $expiryNotificationSend): void
    {
        $this->expiryNotificationSend = $expiryNotificationSend;
    }

    /**
     * @return bool
     */
    public function isReconfirmRequested(): bool
    {
        return $this->reconfirmRequested;
    }

    /**
     * @param bool $reconfirmRequested
     */
    public function setReconfirmRequested(bool $reconfirmRequested): void
    {
        $this->reconfirmRequested = $reconfirmRequested;
    }

    /**
     * @return DateTime|null
     */
    public function getReconfirmRequestedAt(): ?DateTime
    {
        return $this->reconfirmRequestedAt;
    }

    /**
     * @param DateTime|null $reconfirmRequestedAt
     * @return User
     */
    public function setReconfirmRequestedAt(?DateTime $reconfirmRequestedAt): User
    {
        $this->reconfirmRequestedAt = $reconfirmRequestedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNewMessageNotifications(): bool
    {
        return $this->newMessageNotifications;
    }

    /**
     * @param bool $newMessageNotifications
     */
    public function setNewMessageNotifications(bool $newMessageNotifications): void
    {
        $this->newMessageNotifications = $newMessageNotifications;
    }

    /**
     * @return bool
     */
    public function isDisableAdminNotifications(): bool
    {
        return $this->disableAdminNotifications;
    }

    /**
     * @param bool $disableAdminNotifications
     * @return User
     */
    public function setDisableAdminNotifications(bool $disableAdminNotifications): User
    {
        $this->disableAdminNotifications = $disableAdminNotifications;

        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueHash(): string
    {
        return $this->uniqueHash;
    }

    public function __toString()
    {
        return $this->getFullName();
    }
}
