<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
class CompanyRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findCompany(int $companyId)
    {
        $company = $this->entityManager->getRepository(Company::class)->find($companyId);

        if (!$company) {
            die('---------');
        }

        return $company;
    }
}