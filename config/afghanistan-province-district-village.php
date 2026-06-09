<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GeoJSON Data File
    |--------------------------------------------------------------------------
    |
    | Path to the OpenStreetMap GeoJSON export containing village features.
    |
    */

    'geojson_file' => __DIR__.'/../resources/data/export.geojson',

    /*
    |--------------------------------------------------------------------------
    | Provinces & Districts Data File
    |--------------------------------------------------------------------------
    |
    | Path to the JSON file containing province and district admin boundaries.
    |
    */

    'provinces_file' => __DIR__.'/../resources/data/provinces-and-districts.json',

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | Supported: "en", "fa" (Dari), "pa" (Pashto)
    |
    */

    'default_locale' => 'en',

];
