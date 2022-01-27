<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Intl;

/**
 * CountryInformation
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\CountryInformationRepository")
 * @UniqueEntity(fields={"country"}, message="Country info already exists for country {{ value }}", ignoreNull=true)
 *
 * @ORM\Table(name="country_information")
 */
class CountryInformation
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
     * TODO: Make this translatable
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return string
     */
    public function getCountryName(): string
    {
        $countries = Intl::getRegionBundle()->getCountryNames();

        return $countries[$this->country] ?? '';
    }
}
