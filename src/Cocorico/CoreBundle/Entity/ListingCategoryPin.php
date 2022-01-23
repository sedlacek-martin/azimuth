<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingCategoryPin
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="listing_category_pin")
 */
class ListingCategoryPin
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\ListingCategory", mappedBy="pin", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $categories;

    /**
     * @var string
     * @ORM\Column(name="image_path", type="string")
     */
    private $imagePath;

    /**
     * @var string
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     * @return ListingCategoryPin
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     * @return ListingCategoryPin
     */
    public function setImagePath(string $imagePath): ListingCategoryPin
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ListingCategoryPin
     */
    public function setName(string $name): ListingCategoryPin
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) ucfirst($this->getName());
    }
}
