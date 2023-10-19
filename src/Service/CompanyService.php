<?php
// src/Service/CompanyStationService.php
namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompanyService
{
    private $entityManager;

    private $companyRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param CompanyRepository $companyRepository
     */
    public function __construct(EntityManagerInterface $entityManager, CompanyRepository $companyRepository)
    {
        $this->entityManager = $entityManager;
        $this->companyRepository = $companyRepository;

    }

    /**
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data): Company
    {
        $company = new Company();
        $company->setName($data['name']);

        if (isset($data['parent_company_id'])) {
            $parentCompany = $this->companyRepository->findCompany($data['parent_company_id']);
            if ($parentCompany) {
                $company->setParentCompany($parentCompany);
            }
        }

        $this->entityManager->persist($company);
        $this->entityManager->flush();


        return $company;
    }
}
