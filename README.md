# Afghanistan Province District Village

[![Latest Version on Packagist](https://img.shields.io/packagist/v/barialay/afghanistan-province-district-village.svg?style=flat-square)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![Total Downloads](https://img.shields.io/packagist/dt/barialay/afghanistan-province-district-village.svg?style=flat-square)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/packagist/php-v/barialay/afghanistan-province-district-village.svg?style=flat-square)](https://packagist.org/packages/barialay/afghanistan-province-district-village)

A Laravel package for Afghanistan **provinces**, **districts**, and **villages**.

This package includes a full **village database** — **8,892 villages** across all **34 provinces**, each with a name, GPS coordinates, and links to its province and district. No database migrations or seeding required; villages ship as JSON and load at runtime.

Built by [Barialay](https://github.com/Barialay).

## What's included

| Data        | Count   | Details                                              |
|-------------|---------|------------------------------------------------------|
| Provinces   | 34      | English, Dari, and Pashto names + GPS coordinates    |
| Districts   | 400+    | Linked to provinces, multilingual names              |
| **Villages**| **8,892** | **Name, GPS, province ID, district ID** — all provinces |

## Features

- **List all 8,892 villages** or filter by province / district
- **Province → district → village** cascade — ideal for 3-level dropdowns
- Villages include **latitude**, **longitude**, and area data when available
- Find a village by **ID** or **name**
- Multilingual province and district names (English, Dari, Pashto)
- Laravel auto-discovery (Service Provider + `Afghanistan` Facade)
- Publishable config and JSON data files
- Typed objects: `Province`, `District`, `Village`

## Requirements

| PHP | Laravel |
|-----|---------|
| 7.3 – 7.4 | 8.x |
| 8.0+ | 8.x, 9.x, 10.x, 11.x, 12.x |

- **PHP:** `^7.3` or `^8.0`
- **Laravel:** `^8.0` through `^12.0`

> PHP 7.3/7.4 only works with **Laravel 8**. Laravel 9 and above require PHP 8.0+.

## Installation

```bash
composer require barialay/afghanistan-province-district-village:^1.4
```

The package auto-registers. No manual setup needed.

### If Composer blocks installation (security advisories)

If you see errors about `security advisories` on `laravel/framework`, update your Laravel app first — this is not a package issue:

```bash
composer update
composer require barialay/afghanistan-province-district-village:^1.4
```

### Publish config (optional)

```bash
php artisan vendor:publish --tag=afghanistan-province-district-village-config
```

### Publish data files (optional)

```bash
php artisan vendor:publish --tag=afghanistan-province-district-village-data
```

## Villages

The package **lists villages**. Use any of the methods below depending on your UI or API.

### List all villages

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

$allVillages = Afghanistan::villages();       // Collection of 8,892 Village objects
$total       = Afghanistan::countVillages();    // 8892
```

### List villages by province

```php
$kabul = Afghanistan::provinceByName('Kabul');

$villages = Afghanistan::villagesByProvince($kabul->id);
// Every village in Kabul province
```

### List villages by district (recommended for dropdowns)

```php
$kabul = Afghanistan::provinceByName('Kabul');
$districts = Afghanistan::districts($kabul->id);
$kabulDistrict = $districts->firstWhere('name', 'Kabul');

$villages = Afghanistan::villagesByDistrict($kabulDistrict->id);
// Every village in Kabul district — works for all 34 provinces
```

### List villages by province + district name

```php
$villages = Afghanistan::villages('Kabul', 'Kabul');
$villages = Afghanistan::villages('Herat', 'Herat');
$villages = Afghanistan::villages('Badakhshan', 'Wakhan');
```

### Find a single village

```php
$village = Afghanistan::village(4183);
$village = Afghanistan::villageByName('Ab Bala');

echo $village->name;       // Ab Bala
echo $village->province;   // Bamyan
echo $village->district;   // ...
echo $village->latitude;
echo $village->longitude;
```

### Verify villages in Tinker

```bash
php artisan tinker
```

```php
Afghanistan::countVillages();   // 8892
Afghanistan::villages()->count(); // 8892

// Villages exist in every province
Afghanistan::villagesByProvince(Afghanistan::provinceByName('Herat')->id)->count();
Afghanistan::villagesByProvince(Afghanistan::provinceByName('Ghazni')->id)->count();
```

## Province → District → Village cascade

Full 3-level flow for forms and APIs:

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// 1. List provinces
$provinces = Afghanistan::provinces();

// 2. User picks a province → list its districts
$herat = Afghanistan::provinceByName('Herat');
$districts = Afghanistan::districts($herat->id);

// 3. User picks a district → list its villages
$heratDistrict = $districts->firstWhere('name', 'Herat');
$villages = Afghanistan::villagesByDistrict($heratDistrict->id);

foreach ($villages as $village) {
    echo $village->id;
    echo $village->name;
    echo $village->latitude;
    echo $village->longitude;
}
```

### Blade — 3 dropdowns (province, district, village)

```blade
{{-- Province --}}
<select name="province_id" id="province">
    @foreach (Afghanistan::provinces() as $province)
        <option value="{{ $province->id }}">{{ $province->nameFor('fa') }}</option>
    @endforeach
</select>

{{-- District (load via AJAX when province changes) --}}
<select name="district_id" id="district"></select>

{{-- Village (load via AJAX when district changes) --}}
<select name="village_id" id="village"></select>
```

Load districts and villages from your controller:

```php
// GET /districts/{provinceId}
return Afghanistan::districts($provinceId)->map->toArray();

// GET /villages/{districtId}
return Afghanistan::villagesByDistrict($districtId)->map->toArray();
```

### API routes example

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;
use Illuminate\Http\Request;

// GET /api/provinces
public function provinces()
{
    return Afghanistan::provinces()->map->toArray();
}

// GET /api/districts/{provinceId}
public function districts(int $provinceId)
{
    return Afghanistan::districts($provinceId)->map->toArray();
}

// GET /api/villages?district_id=129
public function villages(Request $request)
{
    if ($request->filled('district_id')) {
        return Afghanistan::villagesByDistrict((int) $request->query('district_id'))->map->toArray();
    }

    if ($request->filled('province_id')) {
        return Afghanistan::villagesByProvince((int) $request->query('province_id'))->map->toArray();
    }

    return Afghanistan::villages()->map->toArray();
}
```

## API Reference

### Facade

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Provinces
Afghanistan::provinces();
Afghanistan::province(32);
Afghanistan::provinceByName('Kabul');
Afghanistan::provinceByName('کابل');

// Districts
Afghanistan::districts();
Afghanistan::districts($provinceId);

// Villages — list, filter, find
Afghanistan::villages();                              // all 8,892 villages
Afghanistan::villagesByProvince($provinceId);         // villages in one province
Afghanistan::villagesByDistrict($districtId);         // villages in one district
Afghanistan::villages('Badakhshan', 'Wakhan');        // by province + district name
Afghanistan::village(4183);                           // by ID
Afghanistan::villageByName('Ab Bala');                // by name

// Counts
Afghanistan::countProvinces();   // 34
Afghanistan::countDistricts();
Afghanistan::countVillages();    // 8892
```

### Dependency injection

```php
use Barialay\AfghanistanProvinceDistrictVillage\Afghanistan;

class LocationController
{
    protected $afghanistan;

    public function __construct(Afghanistan $afghanistan)
    {
        $this->afghanistan = $afghanistan;
    }

    public function villagesByDistrict(int $districtId)
    {
        return $this->afghanistan
            ->villagesByDistrict($districtId)
            ->map->toArray();
    }
}
```

## Configuration

```php
// config/afghanistan-province-district-village.php

return [
    'villages_file' => resource_path('afghanistan-province-district-village/villages.json'),
    'provinces_file' => resource_path('afghanistan-province-district-village/provinces-and-districts.json'),
    'default_locale' => 'en', // en, fa, pa
];
```

## Data Structure

### Village

Each of the **8,892 villages** has:

| Field                | Type   | Description                 |
|----------------------|--------|-----------------------------|
| `id`                 | int    | Unique village ID           |
| `name`               | string | Village name                |
| `province`           | string | Province name               |
| `district`           | string | District name               |
| `province_id`        | int    | Linked province ID          |
| `district_id`        | int    | Linked district ID          |
| `latitude`           | float  | GPS latitude                |
| `longitude`          | float  | GPS longitude               |
| `area_square_meters` | float  | Area in m² (when available) |
| `hectares`           | float  | Area in hectares            |

### Province / District

| Field       | Type   | Description    |
|-------------|--------|----------------|
| `id`        | int    | Unique ID      |
| `name`      | string | English name   |
| `name_fa`   | string | Dari name      |
| `name_pa`   | string | Pashto name    |
| `latitude`  | float  | GPS latitude   |
| `longitude` | float  | GPS longitude  |

## Testing

```bash
composer test
```

## Data Disclaimer

Geographic data is compiled from public sources. Some village entries may be incomplete or inaccurate. Always verify critical location data independently.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

Contributions are welcome via Pull Request on [GitHub](https://github.com/Barialay/afghanistan-province-district-village).

## License

This package is open-source software licensed under the [MIT License](LICENSE).

Copyright (c) 2026 [Barialay Rahimi](https://github.com/Barialay).
