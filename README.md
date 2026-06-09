# Afghanistan Province District Village

[![Latest Version on Packagist](https://img.shields.io/packagist/v/barialay/afghanistan-province-district-village.svg?style=flat-square)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![Total Downloads](https://img.shields.io/packagist/dt/barialay/afghanistan-province-district-village.svg?style=flat-square)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/packagist/php-v/barialay/afghanistan-province-district-village.svg?style=flat-square)](https://packagist.org/packages/barialay/afghanistan-province-district-village)

A Laravel package for Afghanistan **provinces**, **districts**, and **villages** — with GPS coordinates, multilingual names (English, Dari, Pashto), and zero database setup.

Built by [Barialay](https://github.com/Barialay).

## Features

- **34 provinces** and **400+ districts** with English, Dari, and Pashto names
- **8,800+ villages** linked to provinces and districts with GPS coordinates
- Full **province → district → village** cascade for dropdowns and APIs
- Laravel auto-discovery (Service Provider + Facade)
- Publishable config and data files
- Data objects: `Province`, `District`, `Village`

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

## Quick Start

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Provinces
$provinces = Afghanistan::provinces();
$kabul = Afghanistan::provinceByName('Kabul');
// or: Afghanistan::provinceByName('کابل')

// Districts in Kabul
$districts = Afghanistan::districts($kabul->id);
$kabulDistrict = $districts->firstWhere('name', 'Kabul');

// Villages in Kabul district
$villages = Afghanistan::villagesByDistrict($kabulDistrict->id);

// Find a village
$village = Afghanistan::villageByName('Ab Bala');
```

### Verify installation (Tinker)

```bash
php artisan tinker
```

```php
Afghanistan::countProvinces();  // 34
Afghanistan::countVillages();   // 8892
```

## Province → District → Village

Use **district ID** for the most reliable village lookup across all provinces:

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// 1. Provinces
$provinces = Afghanistan::provinces();

// 2. Districts for selected province
$herat = Afghanistan::provinceByName('Herat');
$districts = Afghanistan::districts($herat->id);

// 3. Villages for selected district
$heratDistrict = $districts->firstWhere('name', 'Herat');
$villages = Afghanistan::villagesByDistrict($heratDistrict->id);
```

### Blade dropdowns

```blade
<select name="province">
    @foreach (Afghanistan::provinces() as $province)
        <option value="{{ $province->id }}">{{ $province->nameFor('fa') }}</option>
    @endforeach
</select>
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

// Villages
Afghanistan::villages();
Afghanistan::villagesByProvince($provinceId);
Afghanistan::villagesByDistrict($districtId);
Afghanistan::villages('Badakhshan', 'Wakhan');

// Find village
Afghanistan::village(4183);
Afghanistan::villageByName('Ab Bala');

// Counts
Afghanistan::countProvinces();
Afghanistan::countDistricts();
Afghanistan::countVillages();
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

    public function index()
    {
        return response()->json(
            $this->afghanistan->provinces()->map->toArray()
        );
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

Geographic data is compiled from public sources. Some entries may be incomplete or inaccurate. Always verify critical location data independently.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

Contributions are welcome via Pull Request on [GitHub](https://github.com/Barialay/afghanistan-province-district-village).

## License

This package is open-source software licensed under the [MIT License](LICENSE).

Copyright (c) 2026 [Barialay](https://github.com/Barialay).
