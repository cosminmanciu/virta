<?php

// src/Service/ChargingStationService.php
namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Station;

class ChargingStationService
{
    /** @var EntityManagerInterface  */
    private $entityManager;

    /**
     * @var CompanyRepository
     */
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
     * @return Station
     */
    public function createStation(array $data): Station
    {
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

        return $station;
    }

    /**
     * @param array $data
     * @param Station $station
     * @return Station
     */
    public function updateStation(array $data, Station $station): Station
    {
        $station->setName($data['name'] ?? null);

        if (isset($data['company_id'])) {
            $company = $this->companyRepository->findCompany($data['company_id']);
            if ($company) {
                $station->setCompany($company);
            }
        }
        $station->setAddress($data['address'] ?? null);
        $station->setLatitude($data['latitude'] ?? null);
        $station->setLongitude($data['longitude'] ?? null);

        $this->entityManager->persist($station);
        $this->entityManager->flush();

        return $station;
    }

    /**
     * @param int $id
     * @return Station
     */
    public function deleteStation(int $id): Station
    {
        $station = $this->entityManager->getRepository(Station::class)->find($id);

        if (!$station) {
            return $this->json(['error' => 'Station not found'], 404);
        }

        $entityManager = $this->entityManager->getManager();
        $entityManager->remove($station);
        $entityManager->flush();
    }

    /**
     * @param float|null $latitude
     * @param float|null $longitude
     * @param float|null $radiusKm
     * @param int|null $companyId
     * @return array
     */
    public function getStationsInRadius(?float $latitude, ?float $longitude, ?float $radiusKm, ?int $companyId): array
    {
        $stations = $this->fetchStationsForCompanyAndChildrenRecursive($latitude, $longitude, $radiusKm, $companyId);

        usort($stations, function ($a, $b) use ($latitude, $longitude) {
            $distanceA = $this->calculateDistance($latitude, $longitude, $a->getLatitude(), $a->getLongitude());
            $distanceB = $this->calculateDistance($latitude, $longitude, $b->getLatitude(), $b->getLongitude());
            return $distanceA <=> $distanceB;
        });

        return $stations;
    }

    /**
     * @param float|null $latitude
     * @param float|null $longitude
     * @param float|null $radiusKm
     * @param int|null $companyId
     * @return array
     */
    private function fetchStationsForCompanyAndChildrenRecursive(?float $latitude, ?float $longitude, ?float $radiusKm, ?int $companyId): array
    {
        if (isset($companyId)) {
            $stations = $this->fetchStationsForCompany($companyId);
            $childCompanyIds = $this->getChildCompanyIds($companyId);
            foreach ($childCompanyIds as $childCompanyId) {
                $childCompanyStations = $this->fetchStationsForCompanyAndChildrenRecursive($latitude, $longitude, $radiusKm, $childCompanyId);
                $stations = array_merge($stations, $childCompanyStations);
            }
        } else {
            $stations = $this->fetchAllStations();
        }

        return array_filter($stations, function ($station) use ($latitude, $longitude, $radiusKm) {
            $distance = $this->calculateDistance($latitude, $longitude, $station->getLatitude(), $station->getLongitude());
            return $distance <= $radiusKm;
        });
    }

    /**
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @return float|int
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;

    }

    /**
     * @param int $companyId
     * @return mixed
     */
    private function fetchStationsForCompany(int $companyId)
    {
        $repository = $this->entityManager->getRepository(Station::class);

        $stations = $repository->createQueryBuilder('s')
            ->where('s.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getResult();

        return $stations;
    }

    /**
     * @return mixed
     */
    private function fetchAllStations()
    {
        $stations = $this->entityManager->getRepository(Station::class)->findAll();

        return $stations;
    }

    private function getChildCompanyIds(int $parentCompanyId)
    {
        $repository = $this->entityManager->getRepository(Company::class);

        $childCompanies = $repository->createQueryBuilder('c')
            ->where('c.parentCompany = :parentCompanyId')
            ->setParameter('parentCompanyId', $parentCompanyId)
            ->getQuery()
            ->getResult();

        $childCompanyIds = [];
        foreach ($childCompanies as $childCompany) {
            $childCompanyIds[] = $childCompany->getId();
        }

        return $childCompanyIds;
    }
}
