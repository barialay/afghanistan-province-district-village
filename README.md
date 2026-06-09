# Afghanistan Province District Village

[![Latest Version on Packagist](https://img.shields.io/packagist/v/barialay/afghanistan-province-district-village.svg)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![Total Downloads](https://img.shields.io/packagist/dt/barialay/afghanistan-province-district-village.svg)](https://packagist.org/packages/barialay/afghanistan-province-district-village)
[![License](https://img.shields.io/packagist/l/barialay/afghanistan-province-district-village.svg)](https://packagist.org/packages/barialay/afghanistan-province-district-village)

The **Afghanistan province district village** Laravel package — provinces, districts, and villages with GPS coordinates, multilingual names, and zero database setup.

Built by [Barialay](https://github.com/Barialay).

## Features

- **Provinces & districts** with English, Dari, and Pashto names
- **8,800+ villages** linked to provinces and districts with GPS coordinates
- Province → district → village lookup
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

## Province → District → Village

This package supports a cascading flow: pick a province (e.g. کابل / Kabul), then its districts, then villages for that district.

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// 1. List all provinces
$provinces = Afghanistan::provinces();

// 2. User selects Kabul — show its districts
$kabul = Afghanistan::provinceByName('Kabul');
// or by Dari: Afghanistan::provinceByName('کابل')

$districts = Afghanistan::districts($kabul->id);

// 3. User selects a district — show its villages (works for all 34 provinces)
$kabulDistrict = $districts->firstWhere('name', 'Kabul');
$villages = Afghanistan::villagesByDistrict($kabulDistrict->id);

// Same pattern for any province, e.g. Herat, Ghazni, Bamyan, Urozgan:
$herat = Afghanistan::provinceByName('Herat');
$heratDistrict = Afghanistan::districts($herat->id)->firstWhere('name', 'Herat');
$villages = Afghanistan::villagesByDistrict($heratDistrict->id);
```

### Blade example (3 dropdowns)

```blade
{{-- Province --}}
<select name="province" id="province">
    @foreach (Afghanistan::provinces() as $province)
        <option value="{{ $province->id }}">{{ $province->nameFor('fa') }}</option>
    @endforeach
</select>

{{-- District (load via AJAX when province changes) --}}
<select name="district" id="district"></select>

{{-- Village (load via AJAX when district changes) --}}
<select name="village" id="village"></select>
```

### API example

```php
// GET /provinces
public function provinces()
{
    return Afghanistan::provinces()->map->toArray();
}

// GET /districts/{provinceId}
public function districts(int $provinceId)
{
    return Afghanistan::districts($provinceId)->map->toArray();
}

// GET /villages?district_id=129
public function villages(Request $request)
{
    if ($request->filled('district_id')) {
        return Afghanistan::villagesByDistrict((int) $request->query('district_id'))->map->toArray();
    }

    if ($request->filled('province_id')) {
        return Afghanistan::villagesByProvince((int) $request->query('province_id'))->map->toArray();
    }

    return Afghanistan::villages(
        $request->query('province'),
        $request->query('district')
    )->map->toArray();
}
```

## Quick Start

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

$provinces = Afghanistan::provinces();
$kabul = Afghanistan::provinceByName('Kabul');
$districts = Afghanistan::districts($kabul->id);
$villages = Afghanistan::villagesByDistrict($kabulDistrict->id);

$village = Afghanistan::villageByName('Ab Bala');
echo $village->name;
echo $village->latitude;
```

## Usage

### Facade

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Provinces
Afghanistan::provinces();
Afghanistan::provinceByName('Herat');
Afghanistan::provinceByName('کابل'); // works with Dari names too

// Districts
Afghanistan::districts();              // all districts
Afghanistan::districts($herat->id);    // districts in one province

// Villages
Afghanistan::villages();                              // all villages
Afghanistan::villagesByProvince($herat->id);          // by province ID (best for dropdowns)
Afghanistan::villagesByDistrict($districtId);         // by district ID (best for dropdowns)
Afghanistan::villages('Badakhshan', 'Wakhan');        // by province + district name

// Find single village
Afghanistan::village(4183);
Afghanistan::villageByName('Ab Bala');

// Counts
Afghanistan::countProvinces();
Afghanistan::countDistricts();
Afghanistan::countVillages('Kabul', 'Kabul');
```

### Dependency Injection

```php
use Barialay\AfghanistanProvinceDistrictVillage\Afghanistan;

class LocationController
{
    public function __construct(private Afghanistan $afghanistan) {}

    public function index()
    {
        return response()->json([
            'provinces' => $this->afghanistan->provinces()->map->toArray(),
        ]);
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

| Field                | Type   | Description                    |
|----------------------|--------|--------------------------------|
| `id`                 | int    | Unique village ID              |
| `name`               | string | Village name                   |
| `province`           | string | Province name                  |
| `district`           | string | District name                  |
| `province_id`        | int    | Linked province ID             |
| `district_id`        | int    | Linked district ID             |
| `latitude`           | float  | GPS latitude                   |
| `longitude`          | float  | GPS longitude                  |
| `area_square_meters` | float  | Area in m² (when available)    |
| `hectares`           | float  | Area in hectares (when available) |

### Province / District

| Field       | Type   | Description             |
|-------------|--------|-------------------------|
| `id`        | int    | Unique ID               |
| `name`      | string | English name            |
| `name_fa`   | string | Dari name               |
| `name_pa`   | string | Pashto name             |
| `latitude`  | float  | GPS latitude            |
| `longitude` | float  | GPS longitude           |

## Publish to Packagist

1. Create a GitHub repository: `Barialay/afghanistan-province-district-village`
2. Push this package (exclude `vendor/`)
3. Register at [packagist.org](https://packagist.org) with your GitHub URL
4. Users install with:

```bash
composer require barialay/afghanistan-province-district-village
```

## Testing

```bash
composer test
```

## Data Disclaimer

Geographic data is compiled from public sources. Some entries may be incomplete or inaccurate. Always verify critical location data independently.

## Contributing

Issues and pull requests are welcome at [github.com/Barialay/afghanistan-province-district-village](https://github.com/Barialay/afghanistan-province-district-village).

## License

MIT License. See [LICENSE](LICENSE).
