<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VerifiedDomain
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\VerifiedDomainRepository")
 *
 * @ORM\Table(name="verified_domain")
 */
class VerifiedDomain
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
     * @ORM\Column(name="domain", type="string", length=255, nullable=false)
     */
    protected $domain;

    /**
     * @var MemberOrganization|null
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\MemberOrganization")
     */
    protected $memberOrganization;

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
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return MemberOrganization|null
     */
    public function getMemberOrganization(): ?MemberOrganization
    {
        return $this->memberOrganization;
    }

    /**
     * @param MemberOrganization|null $memberOrganization
     */
    public function setMemberOrganization(?MemberOrganization $memberOrganization): void
    {
        $this->memberOrganization = $memberOrganization;
    }

    public function __toString()
    {
        return 'Domain' . ($this->getDomain() ? " ({$this->getDomain()})" : '');
    }
}
