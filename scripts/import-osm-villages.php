<?php

/**
 * Import / enrich village Dari names from OpenStreetMap export.geojson.
 *
 * Usage:
 *   php scripts/import-osm-villages.php           # enrich + add missing
 *   php scripts/import-osm-villages.php --dry-run
 *   php scripts/import-osm-villages.php --enrich-only
 */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

use Barialay\AfghanistanProvinceDistrictVillage\Support\OsmNameNormalizer;
use Barialay\AfghanistanProvinceDistrictVillage\Support\ProvinceNameMapper;

$geojsonPath = $root.'/public/export.geojson';
$villagesPath = $root.'/resources/data/villages.json';
$provincesPath = $root.'/resources/data/provinces-and-districts.json';
$dryRun = in_array('--dry-run', $argv, true);
$enrichOnly = in_array('--enrich-only', $argv, true);
$matchKm = 3.0;
$nameMatchKm = 8.0;
$maxDistrictKm = 40.0;

$geojson = json_decode(file_get_contents($geojsonPath), true);
$villages = json_decode(file_get_contents($villagesPath), true);
$provinces = json_decode(file_get_contents($provincesPath), true);

$districts = buildDistrictIndex($provinces);
$osmPoints = buildOsmPoints($geojson);
$osmByEnglishName = indexOsmByEnglishName($osmPoints);

$nextId = max(array_map(function ($v) {
    return (int) ($v['No'] ?? $v['id'] ?? 0);
}, $villages)) + 1;

$enrichedGps = 0;
$enrichedName = 0;
$enrichedReplaced = 0;
$added = 0;

foreach ($villages as &$village) {
    $englishName = (string) ($village['Village Name'] ?? $village['name'] ?? '');
    $lat = (float) ($village['Latitude'] ?? $village['latitude'] ?? 0);
    $lon = (float) ($village['Longitude'] ?? $village['longitude'] ?? 0);
    $currentFa = isset($village['name_fa']) ? (string) $village['name_fa'] : '';

    $candidate = null;

    if ($lat !== 0.0 || $lon !== 0.0) {
        $candidate = findBestOsmMatch($lat, $lon, $englishName, $osmPoints, $matchKm);
        if ($candidate !== null) {
            $enrichedGps++;
        }
    }

    if ($candidate === null) {
        $candidate = findOsmByEnglishName($englishName, $osmByEnglishName);
        if ($candidate !== null) {
            $enrichedName++;
        }
    }

    if ($candidate === null && ($lat !== 0.0 || $lon !== 0.0)) {
        $candidate = findBestOsmMatch($lat, $lon, $englishName, $osmPoints, $nameMatchKm);
    }

    if ($candidate === null || $candidate['name_fa'] === null) {
        continue;
    }

    $shouldApply = $currentFa === ''
        || OsmNameNormalizer::isLatinLike($currentFa)
        || OsmNameNormalizer::namesMatch($currentFa, $englishName);

    if (! $shouldApply) {
        continue;
    }

    if ($currentFa !== '' && $currentFa !== $candidate['name_fa']) {
        $enrichedReplaced++;
    }

    $village['name_fa'] = $candidate['name_fa'];
}
unset($village);

if (! $enrichOnly) {
    foreach ($osmPoints as $point) {
        if ($point['name_fa'] === null) {
            continue;
        }

        $englishName = $point['name_en'] ?? $point['name_raw'] ?? $point['name_fa'];
        $minKm = PHP_FLOAT_MAX;
        $nearestName = null;

        foreach ($villages as $existing) {
            $lat = (float) ($existing['Latitude'] ?? 0);
            $lon = (float) ($existing['Longitude'] ?? 0);

            if ($lat === 0.0 && $lon === 0.0) {
                continue;
            }

            $distance = haversineKm($point['lat'], $point['lon'], $lat, $lon);

            if ($distance < $minKm) {
                $minKm = $distance;
                $nearestName = (string) ($existing['Village Name'] ?? '');
            }
        }

        $nameAlreadyExists = false;

        foreach ($villages as $existing) {
            $existingName = (string) ($existing['Village Name'] ?? '');

            if (OsmNameNormalizer::namesMatch($existingName, (string) $englishName)) {
                $nameAlreadyExists = true;
                break;
            }
        }

        if ($nameAlreadyExists) {
            continue;
        }

        if ($minKm <= $matchKm && $nearestName !== null
            && OsmNameNormalizer::namesSimilar($nearestName, (string) $englishName)) {
            continue;
        }

        $district = findNearestDistrict($point['lat'], $point['lon'], $districts, $maxDistrictKm);

        if ($district === null) {
            continue;
        }

        $villages[] = [
            'No' => $nextId++,
            'Province' => $district['province_name'],
            'District' => $district['district_name'],
            'Village Name' => $englishName,
            'name_fa' => $point['name_fa'],
            'Latitude' => round($point['lat'], 5),
            'Longitude' => round($point['lon'], 5),
            'source' => 'openstreetmap',
        ];

        $added++;
    }
}

if (! $dryRun) {
    file_put_contents(
        $villagesPath,
        json_encode($villages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL
    );
}

$withDari = 0;
foreach ($villages as $village) {
    if (! empty($village['name_fa']) && OsmNameNormalizer::hasArabicScript((string) $village['name_fa'])) {
        $withDari++;
    }
}

echo json_encode([
    'dry_run' => $dryRun,
    'enrich_only' => $enrichOnly,
    'osm_points' => count($osmPoints),
    'villages_total' => count($villages),
    'enriched_by_gps' => $enrichedGps,
    'enriched_by_name' => $enrichedName,
    'latin_name_fa_replaced' => $enrichedReplaced,
    'osm_villages_added' => $added,
    'villages_with_dari_name' => $withDari,
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
        $nameEn = pickEnglishName($props);

        if ($nameFa === null && $nameEn === null) {
            continue;
        }

        $points[] = [
            'lat' => $coords[1],
            'lon' => $coords[0],
            'name_fa' => $nameFa,
            'name_en' => $nameEn,
            'name_raw' => isset($props['name']) ? (string) $props['name'] : null,
        ];
    }

    return $points;
}

function indexOsmByEnglishName(array $osmPoints): array
{
    $index = [];

    foreach ($osmPoints as $point) {
        foreach ([$point['name_en'], $point['name_raw']] as $label) {
            if ($label === null || $label === '') {
                continue;
            }

            $key = OsmNameNormalizer::normalize($label);
            $index[$key][] = $point;
        }
    }

    return $index;
}

function findOsmByEnglishName(string $englishName, array $index): ?array
{
    $key = OsmNameNormalizer::normalize($englishName);

    if ($key === '' || ! isset($index[$key])) {
        return null;
    }

    foreach ($index[$key] as $point) {
        if ($point['name_fa'] !== null) {
            return $point;
        }
    }

    return $index[$key][0];
}

function findBestOsmMatch(float $lat, float $lon, string $englishName, array $osmPoints, float $maxKm): ?array
{
    $best = null;
    $bestScore = -1.0;

    foreach ($osmPoints as $point) {
        if ($point['name_fa'] === null) {
            continue;
        }

        $distance = haversineKm($lat, $lon, $point['lat'], $point['lon']);

        if ($distance > $maxKm) {
            continue;
        }

        $score = 100.0 - min($distance * 20.0, 80.0);

        if ($point['name_en'] !== null && OsmNameNormalizer::namesMatch($englishName, $point['name_en'])) {
            $score += 50.0;
        } elseif ($point['name_raw'] !== null && OsmNameNormalizer::namesMatch($englishName, $point['name_raw'])) {
            $score += 50.0;
        } elseif ($point['name_en'] !== null && OsmNameNormalizer::namesSimilar($englishName, $point['name_en'])) {
            $score += 30.0;
        }

        if ($score > $bestScore) {
            $bestScore = $score;
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
    foreach (['name:fa', 'name:prs', 'name:ps'] as $key) {
        if (! empty($props[$key]) && OsmNameNormalizer::hasArabicScript((string) $props[$key])) {
            return trim((string) $props[$key]);
        }
    }

    if (! empty($props['name']) && OsmNameNormalizer::hasArabicScript((string) $props['name'])) {
        return trim((string) $props['name']);
    }

    return null;
}

function pickEnglishName(array $props): ?string
{
    if (! empty($props['name:en'])) {
        return trim((string) $props['name:en']);
    }

    if (! empty($props['name']) && OsmNameNormalizer::isLatinLike((string) $props['name'])) {
        return trim((string) $props['name']);
    }

    return null;
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
