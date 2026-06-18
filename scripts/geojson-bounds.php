<?php

$geojson = json_decode(file_get_contents(__DIR__.'/../public/export.geojson'), true);
$lats = [];
$lons = [];

foreach ($geojson['features'] as $feature) {
    $coords = $feature['geometry']['coordinates'] ?? null;
    if ($coords === null) {
        continue;
    }
    if (($feature['geometry']['type'] ?? '') === 'Point') {
        $lons[] = $coords[0];
        $lats[] = $coords[1];
    }
}

echo 'points='.count($lats).PHP_EOL;
echo 'lat '.min($lats).' to '.max($lats).PHP_EOL;
echo 'lon '.min($lons).' to '.max($lons).PHP_EOL;
