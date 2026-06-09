# Afghanistan Province District Village

[![Latest Version on Packagist](https://img.shields.io/packagist/v/barialay/afghanistan-province-district-village.svg)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![Total Downloads](https://img.shields.io/packagist/dt/barialay/afghanistan-province-district-village.svg)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![License](https://img.shields.io/packagist/l/barialay/afghanistan-province-district-village.svg)](https://packagist.org/packages/barialay/afghanistan-province-district-village)

The **Afghanistan province district village** Laravel package — provinces, districts, and villages with GPS coordinates, multilingual names, and zero database setup.

Built by [Barialay](https://github.com/Barialay).

## Features

- **Provinces & districts** with English, Dari, and Pashto names
- **2,900+ villages** loaded from OpenStreetMap `export.geojson`
- GPS coordinates for every location
- Laravel auto-discovery (Service Provider + Facade)
- Publishable config and data files
- Typed PHP objects: `Province`, `District`, `Village`

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require barialay/afghanistan-province-district-village
```

The package auto-registers. No manual setup needed.

### Publish config (optional)

```bash
php artisan vendor:publish --tag=afghanistan-province-district-village-config
```

### Publish data files (optional)

```bash
php artisan vendor:publish --tag=afghanistan-province-district-village-data
```

## Quick Start

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Provinces
$provinces = Afghanistan::provinces();
$kabul = Afghanistan::provinceByName('Kabul');

// Districts
$districts = Afghanistan::districts($kabul->id);

// Villages (from export.geojson)
$villages = Afghanistan::villages();
$village = Afghanistan::village('node/8378744476');
$village = Afghanistan::villageByName('Kesht');

echo $village->nameFor('en'); // Kesht
echo $village->latitude;      // 31.6754293
```

## Usage

### Facade

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// All provinces
Afghanistan::provinces();

// Find province
$herat = Afghanistan::provinceByName('Herat');
echo $herat->nameFor('fa'); // Dari name
echo $herat->nameFor('pa'); // Pashto name

// All districts or by province
Afghanistan::districts();
Afghanistan::districts($herat->id);

// All villages or filter by province/district
Afghanistan::villages();
Afghanistan::villages('Badakhshan');
Afghanistan::villages('Badakhshan', 'Khash rod');

// Find village by OSM ID or name
Afghanistan::village('relation/7784682');
Afghanistan::villageByName('Do Ab');

// Counts
Afghanistan::countProvinces();
Afghanistan::countDistricts();
Afghanistan::countVillages();
```

### Dependency Injection

```php
use Barialay\AfghanistanProvinceDistrictVillage\Afghanistan;

class LocationController
{
    public function __construct(private Afghanistan $afghanistan) {}

    public function provinces()
    {
        return response()->json(
            $this->afghanistan->provinces()->map->toArray()
        );
    }

    public function villages()
    {
        return response()->json(
            $this->afghanistan->villages()->map->toArray()
        );
    }
}
```

### Blade Dropdowns

```php
<select name="province">
    @foreach (Afghanistan::provinces() as $province)
        <option value="{{ $province->id }}">{{ $province->name }}</option>
    @endforeach
</select>
```

## Configuration

```php
// config/afghanistan-province-district-village.php

return [
    'geojson_file' => resource_path('afghanistan-province-district-village/export.geojson'),
    'provinces_file' => resource_path('afghanistan-province-district-village/provinces-and-districts.json'),
    'default_locale' => 'en', // en, fa, pa
];
```

## Data Files

| File | Source | Contents |
|------|--------|----------|
| `export.geojson` | OpenStreetMap (Overpass Turbo) | 2,932 village features with names and GPS |
| `provinces-and-districts.json` | Afghanistan admin divisions | 34 provinces, 400+ districts |

Village data is read directly from your `export.geojson` file. Replace it with an updated export to refresh village data.

## Publish to Packagist

1. Create a GitHub repository: `Barialay/afghanistan-province-district-village`
2. Push this package (exclude `vendor/`)
3. Register at [packagist.org](https://packagist.org) with your GitHub URL
4. Users install with:

```bash
composer require barialay/afghanistan-province-district-village
```

## Updating Village Data

Export a new GeoJSON from [Overpass Turbo](https://overpass-turbo.eu/) for Afghanistan villages, then replace:

```
resources/data/export.geojson
```

Or publish and replace in your Laravel app:

```bash
php artisan vendor:publish --tag=afghanistan-province-district-village-data
```

## Data Structure

### Village (from GeoJSON)

| Field        | Type   | Description                    |
|--------------|--------|--------------------------------|
| `osm_id`     | string | OpenStreetMap feature ID       |
| `name`       | string | Primary name                   |
| `name_en`    | string | English name (`name:en`)       |
| `name_fa`    | string | Dari name (`name:fa`)          |
| `name_ps`    | string | Pashto name (`name:ps`)         |
| `province`   | string | Province (when tagged in OSM)  |
| `district`   | string | District (when tagged in OSM)  |
| `latitude`   | float  | GPS latitude                   |
| `longitude`  | float  | GPS longitude                  |
| `population` | string | Population (when available)  |

### Province / District

| Field       | Type   | Description             |
|-------------|--------|-------------------------|
| `id`        | int    | Unique ID               |
| `name`      | string | English name            |
| `name_fa`   | string | Dari name               |
| `name_pa`   | string | Pashto name             |
| `latitude`  | float  | GPS latitude            |
| `longitude` | float  | GPS longitude           |

## Testing

```bash
composer test
```

## Data Disclaimer

Village data is sourced from [OpenStreetMap](https://www.openstreetmap.org) under [ODbL](https://opendatacommons.org/licenses/odbl/). Some entries may be incomplete or inaccurate. Always verify critical location data independently.

## Contributing

Issues and pull requests are welcome at [github.com/Barialay/afghanistan-province-district-village](https://github.com/Barialay/afghanistan-province-district-village).

## License

MIT License. See [LICENSE](LICENSE).
