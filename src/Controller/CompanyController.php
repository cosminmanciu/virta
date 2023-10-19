<?php

// src/Controller/CompanyController.php
namespace App\Controller;

use App\Service\CompanyService;
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
    private $companyService;

    public function __construct(EntityManagerInterface $entityManager, CompanyRepository $companyRepository, CompanyService $companyService)
    {
        $this->entityManager = $entityManager;
        $this->companyRepository = $companyRepository;
        $this->companyService = $companyService;
    }

    #[Route('/company/create', name: 'create_company',  methods: ['POST'])]
    public function createCompany(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->companyService->createCompany($data);

        return $this->json(['Company created !'], 200);
    }

    #[Route('/company/get/{id}', name: 'get_company',  methods: ['GET'])]
    public function getCompany(int $id): JsonResponse
    {
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find($id);

        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }

        return $this->json(['company' => $company->getName()]);
    }

    #[Route('/company/update/{id}', name: 'update_company',  methods: ['PUT'])]
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

        return $this->json(['message' => 'Company updated']);
    }

    #[Route('/company/delete', name: 'delete_company',  methods: ['DELETE'])]
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
