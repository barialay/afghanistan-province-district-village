<?php

$geojson = json_decode(file_get_contents(__DIR__.'/../public/export.geojson'), true);
$villages = json_decode(file_get_contents(__DIR__.'/../resources/data/villages.json'), true);

$stats = [
    'features' => count($geojson['features']),
    'osm_places' => 0,
    'osm_named' => 0,
    'osm_name_fa' => 0,
    'current_villages' => count($villages),
];

$placeTypes = ['village', 'hamlet', 'locality', 'neighbourhood', 'town'];

foreach ($geojson['features'] as $feature) {
    $props = $feature['properties'] ?? [];
    $place = $props['place'] ?? '';

    if (! in_array($place, $placeTypes, true)) {
        continue;
    }

    $stats['osm_places']++;

    if (! empty($props['name'])) {
        $stats['osm_named']++;
    }

    if (! empty($props['name:fa'])) {
        $stats['osm_name_fa']++;
    }
}

echo json_encode($stats, JSON_PRETTY_PRINT).PHP_EOL;
