<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseListing;
use Cocorico\CoreBundle\Model\ListingOptionInterface;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\TimeBundle\Model\DateRange;
use Cocorico\UserBundle\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Listing
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\ListingRepository")
 *
 * @ORM\Table(name="listing",indexes={
 *    @ORM\Index(name="created_at_l_idx", columns={"createdAt"}),
 *    @ORM\Index(name="status_l_idx", columns={"status"}),
 *    @ORM\Index(name="type_idx", columns={"type"}),
 *  })
 */
class Listing extends BaseListing
{
    use ORMBehaviors\Timestampable\Timestampable;
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     *
     * @var integer
     */
    private $id;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="listings", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="ListingLocation", inversedBy="listing", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var ListingLocation
     **/
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCategory")
     * @ORM\JoinColumn(name="listing_category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $category;

    /**
     * For Asserts @see \Cocorico\CoreBundle\Validator\Constraints\ListingValidator
     *
     * @ORM\OneToMany(targetEntity="ListingImage", mappedBy="listing", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity="ListingListingCharacteristic", mappedBy="listing", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $listingListingCharacteristics;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\MessageBundle\Entity\Thread", mappedBy="listing", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "desc"})
     */
    private $threads;

    /** @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Model\ListingOptionInterface", mappedBy="listing", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $options;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="valid_from", type="date", nullable=true)
     */
    protected $validFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="valid_to", type="date", nullable=true)
     */
    protected $validTo;

    /**
     * @var \DateTime
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

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->listingListingCharacteristics = new ArrayCollection();
        $this->threads = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->expiryDate = (new DateTime())->add(new DateInterval('P1Y'));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add characteristics
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristic
     * @return Listing
     */
    public function addListingListingCharacteristic(ListingListingCharacteristic $listingListingCharacteristic)
    {
        $this->listingListingCharacteristics[] = $listingListingCharacteristic;

        return $this;
    }

    /**
     * Remove characteristics
     *
     * @param \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristic
     */
    public function removeListingListingCharacteristic(ListingListingCharacteristic $listingListingCharacteristic)
    {
        $this->listingListingCharacteristics->removeElement($listingListingCharacteristic);
        $listingListingCharacteristic->setListing(null);
    }

    /**
     * Get characteristics
     *
     * @return \Doctrine\Common\Collections\Collection|ListingListingCharacteristic[]
     */
    public function getListingListingCharacteristics()
    {
        return $this->listingListingCharacteristics;
    }

    /**
     * Get characteristics ordered by Group and Characteristic
     *
     * @return ArrayCollection
     */
    public function getListingListingCharacteristicsOrderedByGroup()
    {
        $iterator = $this->listingListingCharacteristics->getIterator();
        $iterator->uasort(
            function ($a, $b) {
                /**
                 * @var ListingListingCharacteristic $a
                 * @var ListingListingCharacteristic $b
                 */
                $groupPosA = $a->getListingCharacteristic()->getListingCharacteristicGroup()->getPosition();
                $groupPosB = $b->getListingCharacteristic()->getListingCharacteristicGroup()->getPosition();

                $characteristicPosA = $a->getListingCharacteristic()->getPosition();
                $characteristicPosB = $b->getListingCharacteristic()->getPosition();
                if ($groupPosA == $groupPosB) {
                    if ($characteristicPosA == $characteristicPosB) {
                        return 0;
                    }

                    return ($characteristicPosA < $characteristicPosB) ? -1 : 1;
                }

                return ($groupPosA < $groupPosB) ? -1 : 1;
            }
        );

        return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * Add characteristics
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristic
     * @return Listing
     */
    public function addListingListingCharacteristicsOrderedByGroup(
        ListingListingCharacteristic $listingListingCharacteristic
    ) {
        return $this->addListingListingCharacteristic($listingListingCharacteristic);
    }

    /**
     * Remove characteristics
     *
     * @param \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristic
     */
    public function removeListingListingCharacteristicsOrderedByGroup(
        ListingListingCharacteristic $listingListingCharacteristic
    ) {
        $this->removeListingListingCharacteristic($listingListingCharacteristic);
    }

    /**
     * @return ListingCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Listing
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add images
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingImage $image
     * @return Listing
     */
    public function addImage(ListingImage $image)
    {
        $image->setListing($this); //Because the owning side of this relation is listing image
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove images
     *
     * @param \Cocorico\CoreBundle\Entity\ListingImage $image
     */
    public function removeImage(ListingImage $image)
    {
        $this->images->removeElement($image);
        $image->setListing(null);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set location
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingLocation $location
     * @return Listing
     */
    public function setLocation(ListingLocation $location = null)
    {
        $this->location = $location;
        //Needed to persist listing_id on listing_location table when inserting a new listing embedding a listing location form
        $this->location->setListing($this);

        return $this;
    }

    /**
     * Get location
     *
     * @return \Cocorico\CoreBundle\Entity\ListingLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * @param ArrayCollection|Thread[] $threads
     */
    public function setThreads(ArrayCollection $threads)
    {
        foreach ($threads as $thread) {
            $thread->setListing($this);
        }

        $this->threads = $threads;
    }

    /**
     * Add thread
     *
     * @param \Cocorico\MessageBundle\Entity\Thread $thread
     *
     * @return Listing
     */
    public function addThread(Thread $thread)
    {
        $this->threads[] = $thread;

        return $this;
    }

    /**
     * Remove thread
     *
     * @param \Cocorico\MessageBundle\Entity\Thread $thread
     */
    public function removeThread(Thread $thread)
    {
        $this->threads->removeElement($thread);
    }

    /**
     * Add ListingOption
     *
     * @param  ListingOptionInterface $option
     * @return Listing
     */
    public function addOption($option)
    {
        $option->setListing($this);
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove ListingOption
     *
     * @param ListingOptionInterface $option
     */
    public function removeOption($option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get ListingOptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ArrayCollection $options
     * @return $this
     */
    public function setOptions(ArrayCollection $options)
    {
        foreach ($options as $option) {
            $option->setListing($this);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @param int  $minImages
     * @param bool $strict
     *
     * @return array
     */
    public function getCompletionInformations($minImages, $strict = true)
    {
        $characteristic = 0;
        foreach ($this->getListingListingCharacteristics() as $characteristics) {
            if ($characteristics->getListingCharacteristicValue()) {
                $characteristic = 1;
            }
        }

        return [
            'title' => $this->getTitle() ? 1 : 0,
            'description' => (
                ($strict && $this->getDescription()) ||
                (!$strict && strlen($this->getDescription()) > 250)
            ) ? 1 : 0,
            'image' => (
                ($strict && count($this->getImages()) >= $minImages) ||
                (!$strict && count($this->getImages()) > $minImages)
            ) ? 1 : 0,
            'characteristic' => $characteristic,
        ];
    }

    /**
     * @return DateTime|null
     */
    public function getValidFrom(): ?DateTime
    {
        return $this->validFrom;
    }

    /**
     * @param DateTime|null $validFrom
     */
    public function setValidFrom(?DateTime $validFrom): void
    {
        $this->validFrom = $validFrom;
    }

    /**
     * @return DateTime|null
     */
    public function getValidTo(): ?DateTime
    {
        return $this->validTo;
    }

    /**
     * @param DateTime|null $validTo
     */
    public function setValidTo(?DateTime $validTo): void
    {
        $this->validTo = $validTo;
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
        // expiry date changed
        if ($expiryDate !== $this->expiryDate) {
            $this->setExpiryNotificationSend(false);
        }
        $this->expiryDate = $expiryDate;
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
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->getExpiryDate() < new DateTime('now');
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
     * @return Listing
     */
    public function setExpiryNotificationSend(bool $expiryNotificationSend): Listing
    {
        $this->expiryNotificationSend = $expiryNotificationSend;

        return $this;
    }

    /**
     * @return DateRange
     */
    public function getDateRange(): DateRange
    {
        return new DateRange($this->getValidFrom(), $this->getValidTo(), true);
    }

    /**
     * @param DateRange $range
     */
    public function setDateRange(DateRange $range): void
    {
        $this->setValidFrom($range->getStart());
        $this->setValidTo($range->getEnd());
    }

    public function getTitle()
    {
        return (string) $this->translate()->getTitle();
    }

    public function getSlug()
    {
        return (string) $this->translate()->getSlug();
    }

    public function __toString()
    {
        return (string) $this->getTitle();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            //Translations
            $translations = $this->getTranslations();
            $this->translations = new ArrayCollection();
            foreach ($translations as $translation) {
                $this->addTranslation(clone $translation);
            }

            //Images
            $images = $this->getImages();
            $this->images = new ArrayCollection();
            foreach ($images as $image) {
                $this->addImage(clone $image);
            }

            //Location
            $location = $this->getLocation();
            $this->setLocation(clone $location);

            //Characteristics
            $characteristics = $this->getListingListingCharacteristics();
            $this->listingListingCharacteristics = new ArrayCollection();
            foreach ($characteristics as $characteristic) {
                $characteristic = clone $characteristic;
                $characteristic->setListing($this);
                $this->addListingListingCharacteristic($characteristic);
            }

            //Discounts
            $discounts = $this->getDiscounts();
            $this->discounts = new ArrayCollection();
            foreach ($discounts as $discount) {
                $this->addDiscount(clone $discount);
            }

            //Options
            $options = $this->getOptions();
            if ($options) {
                $this->options = new ArrayCollection();
                foreach ($options as $option) {
                    $this->addOption(clone $option);
                }
            }
        }
    }

    /**
     * To add impersonating link into admin :
     *
     * @return User
     */
    public function getImpersonating()
    {
        return $this->getUser();
    }
}
