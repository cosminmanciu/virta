<?php

namespace App\Tests\Service;

use App\Entity\Company;
use App\Entity\Station;
use App\Service\ChargingStationService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChargingStationServiceTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        // Load the Symfony kernel for database access
        self::bootKernel();

        // Get the entity manager from the container
        $this->entityManager = self::$container->get('doctrine.orm.entity_manager');

        // Load Doctrine fixtures to populate the test database
        $this->loadFixtures([AppFixtures::class]);
    }

    public function testGetStationsInRadius()
    {
        // Create a ChargingStationService instance with the real EntityManager
        $chargingStationService = new ChargingStationService($this->entityManager);

        // Perform the unit test
        $stations = $chargingStationService->getStationsInRadius(1.1, 1.1, 1.0, 1);
        $this->assertCount(2, $stations);
    }
}
