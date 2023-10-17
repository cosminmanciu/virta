<?php

// src/Controller/CompanyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    private $entityManager;
    private $companyRepository;

    public function __construct(EntityManagerInterface $entityManager, CompanyRepository $companyRepository)
    {
        $this->entityManager = $entityManager;
        $this->companyRepository = $companyRepository;
    }

    #[Route('/company/create', name: 'create_company')]
    public function createCompany(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        foreach ($requestData as $data) {


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
        }
        return $this->json(['company' => $company]);
    }

    /**
     * Read a single company by ID.
     */
    public function getCompany(int $id): JsonResponse
    {
        $company = $this->companyRepository->findCompany($id);

        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }

        return $this->json(['company' => $company]);
    }

    /**
     * Update a company by ID.
     */
    public function updateCompany(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $company = $this->companyRepository->findCompany($id);

        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }

        if (isset($data['name'])) {
            $company->setName($data['name']);
        }

        if (isset($data['parent_company_id'])) {
            $parentCompany = $this->companyRepository->findCompany($data['parent_company_id']);
            if ($parentCompany) {
                $company->setParentCompany($parentCompany);
            }
        }

        $this->entityManager->flush();

        return $this->json(['company' => $company]);
    }

    /**
     * Delete a company by ID.
     */
    public function deleteCompany(int $id): JsonResponse
    {
        $company = $this->companyRepository->findCompany($id);

        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }

        $this->entityManager->remove($company);
        $this->entityManager->flush();

        return $this->json(['message' => 'Company deleted']);
    }
}
