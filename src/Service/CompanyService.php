<?php
// src/Service/CompanyStationService.php
namespace App\Service;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class CompanyService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStationsForCompanyAndChildren(Company $company): array
    {
        $stations = [];
        $this->fetchStationsForCompanyAndChildren($company, $stations);
        return $stations;
    }

    private function fetchStationsForCompanyAndChildren(Company $company, array &$stations)
    {
// Fetch stations for the current company
        $companyStations = $company->getStations();
        foreach ($companyStations as $station) {
            $stations[] = $station;
        }

// Recursively fetch stations for child companies
        $childCompanies = $this->entityManager->getRepository(Company::class)->findBy(['parentCompany' => $company]);
        foreach ($childCompanies as $childCompany) {
            $this->fetchStationsForCompanyAndChildren($childCompany, $stations);
        }
    }
}
