<?php

/**
 * Import village names from OpenStreetMap (export.geojson).
 *
 * - Adds Dari names (name_fa) to existing villages by GPS proximity
 * - Adds OSM villages not already in the dataset (nearest district assignment)
 *
 * Source: public/export.geojson (ODbL — OpenStreetMap, not Google Earth)
 *
 * Usage: php scripts/import-osm-villages.php [--dry-run]
 */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

use Barialay\AfghanistanProvinceDistrictVillage\Support\ProvinceNameMapper;

$geojsonPath = $root.'/public/export.geojson';
$villagesPath = $root.'/resources/data/villages.json';
$provincesPath = $root.'/resources/data/provinces-and-districts.json';
$dryRun = in_array('--dry-run', $argv, true);
$matchKm = 1.5;
$maxDistrictKm = 40.0;

$geojson = json_decode(file_get_contents($geojsonPath), true);
$villages = json_decode(file_get_contents($villagesPath), true);
$provinces = json_decode(file_get_contents($provincesPath), true);

$districts = buildDistrictIndex($provinces);
$osmPoints = buildOsmPoints($geojson);
$nextId = max(array_map(function ($v) {
    return (int) ($v['No'] ?? $v['id'] ?? 0);
}, $villages)) + 1;

$enriched = 0;
$added = 0;

foreach ($villages as &$village) {
    $lat = (float) ($village['Latitude'] ?? $village['latitude'] ?? 0);
    $lon = (float) ($village['Longitude'] ?? $village['longitude'] ?? 0);

    if ($lat === 0.0 && $lon === 0.0) {
        continue;
    }

    $nearest = findNearestOsm($lat, $lon, $osmPoints, $matchKm);

    if ($nearest === null) {
        continue;
    }

    if (empty($village['name_fa']) && $nearest['name_fa'] !== null) {
        $village['name_fa'] = $nearest['name_fa'];
        $enriched++;
    }
}
unset($village);

$existingCoords = array_map(function ($v) {
    return [
        'lat' => (float) ($v['Latitude'] ?? 0),
        'lon' => (float) ($v['Longitude'] ?? 0),
    ];
}, $villages);

foreach ($osmPoints as $point) {
    if ($point['name_fa'] === null && $point['name_en'] === null) {
        continue;
    }

    $minKm = PHP_FLOAT_MAX;

    foreach ($existingCoords as $coord) {
        if ($coord['lat'] === 0.0 && $coord['lon'] === 0.0) {
            continue;
        }

        $distance = haversineKm($point['lat'], $point['lon'], $coord['lat'], $coord['lon']);

        if ($distance < $minKm) {
            $minKm = $distance;
        }
    }

    if ($minKm <= $matchKm) {
        continue;
    }

    $district = findNearestDistrict($point['lat'], $point['lon'], $districts, $maxDistrictKm);

    if ($district === null) {
        continue;
    }

    $englishName = $point['name_en'] ?? $point['name_fa'];
    $villages[] = [
        'No' => $nextId++,
        'Province' => $district['province_name'],
        'District' => $district['district_name'],
        'Village Name' => $englishName,
        'name_fa' => $point['name_fa'] ?? $englishName,
        'Latitude' => round($point['lat'], 5),
        'Longitude' => round($point['lon'], 5),
        'source' => 'openstreetmap',
    ];

    $existingCoords[] = ['lat' => $point['lat'], 'lon' => $point['lon']];
    $added++;
}

if (! $dryRun) {
    file_put_contents(
        $villagesPath,
        json_encode($villages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL
    );
}

echo json_encode([
    'dry_run' => $dryRun,
    'osm_points' => count($osmPoints),
    'villages_before' => count($villages) - $added,
    'villages_after' => count($villages),
    'name_fa_enriched' => $enriched,
    'osm_villages_added' => $added,
], JSON_PRETTY_PRINT).PHP_EOL;

function buildDistrictIndex(array $provinces): array
{
    $districts = [];

    foreach ($provinces as $province) {
        foreach ($province['districts'] ?? [] as $district) {
            $lat = (float) ($district['latitude'] ?? 0);
            $lon = (float) ($district['longitude'] ?? 0);

            if ($lat === 0.0 && $lon === 0.0) {
                continue;
            }

            $districts[] = [
                'id' => (int) $district['id'],
                'district_name' => (string) $district['name'],
                'province_name' => ProvinceNameMapper::villageNameForAdmin((string) $province['name']),
                'lat' => $lat,
                'lon' => $lon,
            ];
        }
    }

    return $districts;
}

function buildOsmPoints(array $geojson): array
{
    $points = [];

    foreach ($geojson['features'] as $feature) {
        $props = $feature['properties'] ?? [];

        if (! in_array($props['place'] ?? '', ['village', 'hamlet', 'locality'], true)) {
            continue;
        }

        $coords = extractCoordinates($feature['geometry'] ?? []);

        if ($coords === null) {
            continue;
        }

        $nameFa = pickDariName($props);
        $nameEn = null;

        if (! empty($props['name:en'])) {
            $nameEn = trim((string) $props['name:en']);
        } elseif (! empty($props['name']) && isLatin((string) $props['name'])) {
            $nameEn = trim((string) $props['name']);
        }

        if ($nameFa === null && $nameEn === null) {
            continue;
        }

        $points[] = [
            'lat' => $coords[1],
            'lon' => $coords[0],
            'name_fa' => $nameFa,
            'name_en' => $nameEn,
        ];
    }

    return $points;
}

function findNearestOsm(float $lat, float $lon, array $osmPoints, float $maxKm): ?array
{
    $best = null;
    $bestDistance = $maxKm;

    foreach ($osmPoints as $point) {
        $distance = haversineKm($lat, $lon, $point['lat'], $point['lon']);

        if ($distance < $bestDistance) {
            $bestDistance = $distance;
            $best = $point;
        }
    }

    return $best;
}

function findNearestDistrict(float $lat, float $lon, array $districts, float $maxKm): ?array
{
    $best = null;
    $bestDistance = $maxKm;

    foreach ($districts as $district) {
        $distance = haversineKm($lat, $lon, $district['lat'], $district['lon']);

        if ($distance < $bestDistance) {
            $bestDistance = $distance;
            $best = $district;
        }
    }

    return $best;
}

function extractCoordinates(array $geometry): ?array
{
    $type = $geometry['type'] ?? '';

    if ($type === 'Point') {
        return $geometry['coordinates'];
    }

    if ($type === 'Polygon' && ! empty($geometry['coordinates'][0][0])) {
        return $geometry['coordinates'][0][0];
    }

    return null;
}

function pickDariName(array $props): ?string
{
    if (! empty($props['name:fa'])) {
        return trim((string) $props['name:fa']);
    }

    if (! empty($props['name']) && ! isLatin((string) $props['name'])) {
        return trim((string) $props['name']);
    }

    if (! empty($props['name:ps'])) {
        return trim((string) $props['name:ps']);
    }

    return null;
}

function isLatin(string $value): bool
{
    return (bool) preg_match('/^[a-zA-Z0-9\s\-\.\(\)]+$/u', $value);
}

function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

    return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
}
