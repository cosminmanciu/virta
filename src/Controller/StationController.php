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

class StationController extends AbstractController
{
    private $chargingStationService;

    private $companyRepository;

    private $entityManager;

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

    #[Route('/station/get', name: 'get_stations')]
    public function getStationsInRadius(Request $request): JsonResponse
    {
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');
        $radiusKm = $request->query->get('radius', 10);
        $companyId = $request->query->get('company_id');

        $stations = $this->chargingStationService->getStationsInRadius($latitude, $longitude, $radiusKm, $companyId);

        return $this->json(['stations' => $stations]);
    }

//    /**
//     * Get charging stations for a company and its children.
//     */
//    public function getStationsForCompany(Request $request, int $companyId): JsonResponse
//    {
//        $company = $this->getDoctrine()->getRepository(Company::class)->find($companyId);
//
//        if (!$company) {
//            return $this->json(['error' => 'Company not found'], 404);
//        }
//
//        $stations = $this->companyStationService->getStationsForCompanyAndChildren($company);
//
//        return $this->json(['stations' => $stations]);
//    }
    #[Route('/station/create', name: 'station_company')]
    public function createStation(Request $request): JsonResponse
    {
        $requestDta = json_decode($request->getContent(), true);
        foreach ($requestDta as $data) {
            $station = new Station();
            $station->setName($data['name']);

            if (isset($data['company_id'])) {
                $company = $this->companyRepository->findCompany($data['company_id']);
                if ($company) {
                    $station->setCompany($company);
                }
            }
            $station->setAddress($data['address']);
            $station->setLatitude($data['latitude']);
            $station->setLongitude($data['longitude']);

            $this->entityManager->persist($station);
            $this->entityManager->flush();
        }
        return $this->json(['station' => $station]);
    }

    /**
     * Read a single station by ID.
     */
//    public function getStation(int $id): JsonResponse
//    {
//        $station = $this->getDoctrine()->getRepository(Station::class)->find($id);
//
//        if (!$station) {
//            return $this->json(['error' => 'Station not found'], 404);
//        }
//
//        return $this->json(['station' => $station]);
//    }

    /**
     * Update a station by ID.
     */
    public function updateStation(Request $request, int $id): JsonResponse
    {
        // Implement station update logic here
    }

    /**
     * Delete a station by ID.
     */
    public function deleteStation(int $id): JsonResponse
    {
        $station = $this->entityManager->getRepository(Station::class)->find($id);

        if (!$station) {
            return $this->json(['error' => 'Station not found'], 404);
        }

        $entityManager = $this->entityManager->getManager();
        $entityManager->remove($station);
        $entityManager->flush();

        return $this->json(['message' => 'Station deleted']);
    }
}
