<?php

$root = dirname(__DIR__);
$geojson = json_decode(file_get_contents($root.'/public/export.geojson'), true);
$villages = json_decode(file_get_contents($root.'/resources/data/villages.json'), true);
$maxDistanceKm = 1.5;

$existing = array_map(function ($v) {
    return [
        'lat' => (float) ($v['Latitude'] ?? 0),
        'lon' => (float) ($v['Longitude'] ?? 0),
    ];
}, $villages);

$osmOnly = 0;
$osmNamedOnly = 0;

foreach ($geojson['features'] as $feature) {
    $props = $feature['properties'] ?? [];
    if (! in_array($props['place'] ?? '', ['village', 'hamlet', 'locality'], true)) {
        continue;
    }

    $geometry = $feature['geometry'] ?? [];
    $coords = $geometry['type'] === 'Point'
        ? $geometry['coordinates']
        : ($geometry['coordinates'][0][0] ?? null);

    if ($coords === null) {
        continue;
    }

    $lat = $coords[1];
    $lon = $coords[0];
    $min = PHP_FLOAT_MAX;

    foreach ($existing as $point) {
        if ($point['lat'] === 0.0 && $point['lon'] === 0.0) {
            continue;
        }
        $dLat = deg2rad($point['lat'] - $lat);
        $dLon = deg2rad($point['lon'] - $lon);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad($point['lat'])) * sin($dLon / 2) ** 2;
        $distance = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
        if ($distance < $min) {
            $min = $distance;
        }
    }

    if ($min > $maxDistanceKm) {
        $osmOnly++;
        if (! empty($props['name']) || ! empty($props['name:fa'])) {
            $osmNamedOnly++;
        }
    }
}

echo json_encode([
    'osm_not_near_existing_village_km' => $maxDistanceKm,
    'osm_only_places' => $osmOnly,
    'osm_only_named' => $osmNamedOnly,
], JSON_PRETTY_PRINT).PHP_EOL;
