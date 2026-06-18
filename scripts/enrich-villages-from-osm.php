<?php

/**
 * Enrich villages.json with Dari names from OpenStreetMap export.geojson.
 * OSM data is ODbL-licensed (see export.geojson copyright header).
 */

$root = dirname(__DIR__);
$geojsonPath = $root.'/public/export.geojson';
$villagesPath = $root.'/resources/data/villages.json';
$dryRun = in_array('--dry-run', $argv, true);
$maxDistanceKm = 1.5;

$geojson = json_decode(file_get_contents($geojsonPath), true);
$villages = json_decode(file_get_contents($villagesPath), true);

$osmPoints = [];

foreach ($geojson['features'] as $feature) {
    $props = $feature['properties'] ?? [];
    $place = $props['place'] ?? '';

    if (! in_array($place, ['village', 'hamlet', 'locality'], true)) {
        continue;
    }

    $coords = extractCoordinates($feature['geometry'] ?? []);

    if ($coords === null) {
        continue;
    }

    $nameFa = pickDariName($props);

    if ($nameFa === null) {
        continue;
    }

    $osmPoints[] = [
        'lat' => $coords[1],
        'lon' => $coords[0],
        'name_fa' => $nameFa,
        'name_en' => $props['name:en'] ?? (isLatin($props['name'] ?? '') ? $props['name'] : null),
    ];
}

$matched = 0;
$alreadyHad = 0;

foreach ($villages as &$village) {
    if (! empty($village['name_fa'])) {
        $alreadyHad++;

        continue;
    }

    $lat = (float) ($village['Latitude'] ?? $village['latitude'] ?? 0);
    $lon = (float) ($village['Longitude'] ?? $village['longitude'] ?? 0);

    if ($lat === 0.0 && $lon === 0.0) {
        continue;
    }

    $best = null;
    $bestDistance = $maxDistanceKm;

    foreach ($osmPoints as $point) {
        $distance = haversineKm($lat, $lon, $point['lat'], $point['lon']);

        if ($distance < $bestDistance) {
            $bestDistance = $distance;
            $best = $point;
        }
    }

    if ($best !== null) {
        $village['name_fa'] = $best['name_fa'];
        $matched++;
    }
}

if (! $dryRun) {
    file_put_contents(
        $villagesPath,
        json_encode($villages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL
    );
}

echo json_encode([
    'osm_points_with_dari' => count($osmPoints),
    'villages_total' => count($villages),
    'newly_matched' => $matched,
    'already_had_name_fa' => $alreadyHad,
], JSON_PRETTY_PRINT).PHP_EOL;

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
