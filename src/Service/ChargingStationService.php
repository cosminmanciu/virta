<?php

// src/Service/ChargingStationService.php
namespace App\Service;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Station;

class ChargingStationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStationsInRadius(float $latitude, float $longitude, float $radiusKm, int $companyId): array
    {
        $stations = $this->fetchStationsForCompanyAndChildrenRecursive($latitude, $longitude, $radiusKm, $companyId);

        usort($stations, function ($a, $b) use ($latitude, $longitude) {
            $distanceA = $this->calculateDistance($latitude, $longitude, $a->getLatitude(), $a->getLongitude());
            $distanceB = $this->calculateDistance($latitude, $longitude, $b->getLatitude(), $b->getLongitude());
            return $distanceA <=> $distanceB;
        });

        return $stations;
    }

    private function fetchStationsForCompanyAndChildrenRecursive(float $latitude, float $longitude, float $radiusKm, int $companyId): array
    {
        $stations = $this->fetchStationsForCompany($companyId);

        $childCompanyIds = $this->getChildCompanyIds($companyId);

        foreach ($childCompanyIds as $childCompanyId) {
            $childCompanyStations = $this->fetchStationsForCompanyAndChildrenRecursive($latitude, $longitude, $radiusKm, $childCompanyId);
            $stations = array_merge($stations, $childCompanyStations);
        }

        return array_filter($stations, function ($station) use ($latitude, $longitude, $radiusKm) {
            $distance = $this->calculateDistance($latitude, $longitude, $station->getLatitude(), $station->getLongitude());
            return $distance <= $radiusKm;
        });
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;

    }

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
