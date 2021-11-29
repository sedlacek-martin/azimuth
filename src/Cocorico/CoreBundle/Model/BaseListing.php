<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Listing
 *
 * @CocoricoAssert\Listing()
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseListing
{

    /* Status */
    const STATUS_NEW = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_INVALIDATED = 3;
    const STATUS_SUSPENDED = 4;
    const STATUS_DELETED = 5;
    const STATUS_TO_VALIDATE = 6;

    public static $statusValues = array(
        self::STATUS_NEW => 'entity.listing.status.new',
        self::STATUS_PUBLISHED => 'entity.listing.status.published',
        self::STATUS_INVALIDATED => 'entity.listing.status.invalidated',
        self::STATUS_SUSPENDED => 'entity.listing.status.suspended',
        self::STATUS_DELETED => 'entity.listing.status.deleted',
        self::STATUS_TO_VALIDATE => 'entity.listing.status.to_validate'
    );

    public static $visibleStatus = array(
        self::STATUS_NEW,
        self::STATUS_PUBLISHED,
        self::STATUS_INVALIDATED,
        self::STATUS_SUSPENDED,
        self::STATUS_TO_VALIDATE
    );

    public static $activeStatus = [
        self::STATUS_NEW,
        self::STATUS_PUBLISHED,
        self::STATUS_TO_VALIDATE,
    ];


    /* Type */
    const OFFER = 1;
    const SEARCH = 2;

    public static $typeValues = array(
        self::OFFER => 'entity.listing.type.offer',
        self::SEARCH => 'entity.listing.type.search',
    );

    /**
     * @ORM\Column(name="status", type="smallint", nullable=false)
     *
     * @var integer
     */
    protected $status = self::STATUS_NEW;

    /**
     * @ORM\Column(name="type", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $type = self::OFFER;

    /**
     *
     * @ORM\Column(name="certified", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $certified;

    /**
     * Admin notation
     *
     * @ORM\Column(name="admin_notation", type="decimal", precision=3, scale=1, nullable=true)
     *
     * @var float
     */
    protected $adminNotation;

    /**
     * Translation proxy
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Set status
     *
     * @param  integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
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
     * Return available status for current status
     *
     * @param int $status
     *
     * @return array
     */
    public static function getAvailableStatusValues($status)
    {
        $availableStatus = array(self::STATUS_DELETED);

        if ($status == self::STATUS_NEW) {
            $availableStatus[] = self::STATUS_PUBLISHED;
        } elseif ($status == self::STATUS_PUBLISHED) {
            $availableStatus[] = self::STATUS_SUSPENDED;
        } elseif ($status == self::STATUS_INVALIDATED) {
            $availableStatus[] = self::STATUS_TO_VALIDATE;
        } elseif ($status == self::STATUS_SUSPENDED) {
            $availableStatus[] = self::STATUS_PUBLISHED;
        }

        //Prepend current status to visible status
        array_unshift($availableStatus, $status);

        //Construct associative array with keys equals to status values and values to label of status
        $status = array_intersect_key(
            self::$statusValues,
            array_flip($availableStatus)
        );

        return $status;
    }

    /**
     * @return bool
     */
    public function isToValidate(): bool
    {
        return $this->getStatus() === self::STATUS_TO_VALIDATE;
    }


    /**
     * @return boolean
     */
    public function isCertified()
    {
        return $this->certified;
    }

    /**
     * @param boolean $certified
     */
    public function setCertified($certified)
    {
        $this->certified = $certified;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param  integer $type
     * @return $this
     */
    public function setType($type)
    {
        if (!in_array($type, array_keys(self::$typeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.type : %s.', $type)
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get Type Text
     *
     * @return string
     */
    public function getTypeText()
    {
        return self::$typeValues[$this->getType()];
    }

    /**
     * Get certified
     *
     * @return boolean
     */
    public function getCertified()
    {
        return $this->certified;
    }

    /**
     * @return float
     */
    public function getAdminNotation()
    {
        return $this->adminNotation;
    }

    /**
     * @param float $adminNotation
     */
    public function setAdminNotation($adminNotation)
    {
        $this->adminNotation = $adminNotation;
    }
}
