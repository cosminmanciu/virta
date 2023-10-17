<?php

// src/Entity/Company.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Company
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="childCompanies")
     */
    private $parentCompany;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Station", mappedBy="company")
     */
    private $stations;

    /**
     * @ORM\OneToMany(targetEntity="Company", mappedBy="parentCompany")
     */
    private $childCompanies;

    public function __construct()
    {
        $this->stations = new ArrayCollection();
        $this->childCompanies = new ArrayCollection();
    }

    // Getters and Setters for all properties

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentCompany(): ?Company
    {
        return $this->parentCompany;
    }

    public function setParentCompany(?Company $parentCompany): void
    {
        $this->parentCompany = $parentCompany;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStations()
    {
        return $this->stations;
    }

    public function getChildCompanies()
    {
        return $this->childCompanies;
    }
}
