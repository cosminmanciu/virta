<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
class CompanyRepository
{
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $companyId
     * @return void
     */
    public function findCompany(int $companyId): Company|array
    {
        $company = $this->entityManager->getRepository(Company::class)->find($companyId);

        if (!$company) {
            return [
                'message' => 'Company not found !'
            ];
        }

        return $company;
    }
}