<?php

namespace App\Controller;


// src/Controller/StationController.php
use App\Entity\Company;
use App\Entity\Station;
use App\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\CompanyStationService;
use App\Service\ChargingStationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\Annotation\ApiResource;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;
class StationController extends AbstractController
{
    private $chargingStationService;

    private $companyRepository;

    private $entityManager;

    /**
     * @param ChargingStationService $chargingStationService
     * @param CompanyRepository $companyRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ChargingStationService $chargingStationService,
        CompanyRepository      $companyRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $this->chargingStationService = $chargingStationService;
        $this->companyRepository = $companyRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('station/get', name: 'get_stations',methods: ['GET'])]
    public function getStationsInRadius(Request $request): JsonResponse
    {
        $stationList = [];
        $latitude = $request->query->get('latitude') ?? null;
        $longitude = $request->query->get('longitude') ?? null;
        $radiusKm = $request->query->get('radius', 10);
        $companyId = $request->query->get('company_id') ?? null;

        $stations = $this->chargingStationService->getStationsInRadius($latitude, $longitude, $radiusKm, $companyId);

        /** @var Station $station */
        foreach ($stations as $station) {
            $stationList[$station->getId()] = [
                'name' => $station->getName(),
                'latitude' => $station->getLatitude(),
                'longitude' => $station->getLongitude(),
                'company' => [
                    'id' => $station->getCompany()->getId(),
                    'name' => $station->getCompany()->getName(),
                    //'parent' => $station->getCompany() ? ->getParentCompany()->getName(),
                ],
                'address' => $station->getAddress(),
            ];

        }


        return $this->json(['stations' => $stationList]);
    }

    #[Route('station/create', name: 'create_station', methods: ['POST'])]
    public function createStation(Request $request): JsonResponse
    {
        $requestDta = json_decode($request->getContent(), true);


        $station = $this->chargingStationService->createStation($requestDta);

        return $this->json(['Station created'], 200);
    }


    #[Route('station/update/{id}', name: 'update_station', methods: ['PUT'])]
    public function updateStation(Request $request, int $id): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $station = $this->entityManager->getRepository(Station::class)->find($id);

        if (!$station) {
            return $this->json(['error' => 'Station not found'], 404);
        }
        $this->chargingStationService->updateStation($requestData, $station);
        return $this->json(['sucess' => 'Station updated'], 200);
    }

    #[Route('station/delete/{id}', name: 'delete_station', methods: ['DELETE'])]
    public function deleteStation(int $id): JsonResponse
    {
        $this->chargingStationService->deleteStation($id);

        return $this->json(['message' => 'Station deleted'], 200);
    }
}
