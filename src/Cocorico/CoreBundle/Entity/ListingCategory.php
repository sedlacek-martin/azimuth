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

use Cocorico\CoreBundle\Model\BaseListingCategory;
use Cocorico\CoreBundle\Model\ListingCategoryListingCategoryFieldInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingCategory
 *
 * @Gedmo\Tree(type="nested")
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\ListingCategoryRepository")
 *
 * @ORM\Table(name="listing_category")
 *
 */
class ListingCategory extends BaseListingCategory
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ListingCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="ListingCategory", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     *
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Model\ListingCategoryListingCategoryFieldInterface", mappedBy="category", cascade={"persist", "remove"})
     */
    private $fields;


    /**
     * @var ListingCategoryPin|null
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCategoryPin", inversedBy="categories")
     * @ORM\JoinColumn(name="category_pin_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $pin;

    /**
     * @ORM\Column(name="offer", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $offer = true;

    /**
     * @ORM\Column(name="search", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $search = true;

    /**
     * @var ListingCharacteristic[]|Collection
     * @ORM\ManyToMany(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristic", mappedBy="listingCategories")
     */
    protected $characteristics;

    /**
     * @ORM\Column(type="string", length=255, name="default_image_name", nullable=false)
     *
     * @var string|null $defaultImageName;
     */
    protected $defaultImageName;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="assert.not_blank")
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    private $position;


    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->characteristics = new ArrayCollection();
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
     * Set parent
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCategory $parent
     * @return ListingCategory
     */
    public function setParent(ListingCategory $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Cocorico\CoreBundle\Entity\ListingCategory
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * Add children
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCategory $children
     * @return ListingCategory
     */
    public function addChild(ListingCategory $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Cocorico\CoreBundle\Entity\ListingCategory $children
     */
    public function removeChild(ListingCategory $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add field
     *
     * @param  ListingCategoryListingCategoryFieldInterface $field
     * @return ListingCategory
     */
    public function addField($field)
    {
        $field->setCategory($this);
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove listings
     *
     * @param  ListingCategoryListingCategoryFieldInterface $field
     */
    public function removeField($field)
    {
        $this->fields->removeElement($field);
    }

    /**
     * Get fields
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|ListingCategoryListingCategoryFieldInterface[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function getName()
    {
        return $this->translate()->getName();
    }

    /**
     * @return ListingCategoryPin|null
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @return ListingCategory
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOffer(): bool
    {
        return $this->offer;
    }

    /**
     * @param bool $offer
     */
    public function setOffer(bool $offer): void
    {
        $this->offer = $offer;
    }

    /**
     * @return bool
     */
    public function isSearch(): bool
    {
        return $this->search;
    }

    /**
     * @param bool $search
     */
    public function setSearch(bool $search): void
    {
        $this->search = $search;
    }

    /**
     * @return ListingCharacteristic[]|Collection
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }

    /**
     * @param ListingCharacteristic[]|Collection $characteristics
     */
    public function setCharacteristics($characteristics): void
    {
        $this->characteristics = $characteristics;
    }

    /**
     * @return string|null
     */
    public function getDefaultImageName(): ?string
    {
        return $this->defaultImageName;
    }

    /**
     * @param string $defaultImageName
     * @return ListingCategory
     */
    public function setDefaultImageName(string $defaultImageName): ListingCategory
    {
        $this->defaultImageName = $defaultImageName;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return ListingCategory
     */
    public function setPosition(int $position): ListingCategory
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
