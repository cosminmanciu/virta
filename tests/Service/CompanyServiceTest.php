<?php

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use App\Service\CompanyService;

class CompanyServiceTest extends TestCase
{
    // Create a mock for the EntityManager
    private $entityManager;

    // Create a mock for the CompanyRepository
    private $companyRepository;

    // Instance of the CompanyService
    private $companyService;

    protected function setUp(): void
    {
        // Initialize the EntityManager mock
        $this->entityManager = $this->createMock(EntityManager::class);

        // Initialize the CompanyRepository mock
        $this->companyRepository = $this->createMock(CompanyRepository::class);

        // Create an instance of the CompanyService and pass the mocks
        $this->companyService = new CompanyService($this->entityManager, $this->companyRepository);
    }

    public function testCreateCompany()
    {
        // Define your test data
        $requestData = [
            'name' => 'Company B',
            'parent_company_id' => 1,
        ];

        // Create expectations for the EntityManager mock
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Company::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Create expectations for the CompanyRepository mock (if necessary)
        $this->companyRepository->expects($this->any())
            ->method('findCompany')
            ->willReturnCallback(function ($parentCompanyId) {
                // Mock the behavior of findCompany if needed
                // For example, return a mock Company object
                if ($parentCompanyId === 1) {
                    $company = new Company();
                    $company->setName('Parent Company');
                    return $company;
                }
                return null; // Return null for other cases
            });

        // Call the method under test
        $result = $this->companyService->createCompany($requestData);

        // Assert that the method returned the last created company
        $this->assertInstanceOf(Company::class, $result);
    }
}
