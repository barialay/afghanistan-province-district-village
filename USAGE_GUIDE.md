# Afghanistan Province District Village - Usage Guide

## Quick Start

This guide provides comprehensive examples for using the Afghanistan Province District Village Laravel package.

### Installation

```bash
composer require barialay/afghanistan-province-district-village:^1.4
```

## Basic Usage

### Getting Started with Provinces

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Get all provinces
$provinces = Afghanistan::provinces();

// Get a specific province by ID
$kabul = Afghanistan::province(1);

// Get a province by name
$herat = Afghanistan::provinceByName('Herat');

// Get province by Dari name
$panjshir = Afghanistan::provinceByName('پنجشیر');

// Count total provinces
$totalProvinces = Afghanistan::countProvinces(); // 34
```

### Working with Districts

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Get all districts
$allDistricts = Afghanistan::districts();

// Get districts for a specific province
$kabuProvinceId = 1;
$kabulDistricts = Afghanistan::districts($kabuProvinceId);

// Filter districts by name
$districtByName = $kabulDistricts->firstWhere('name', 'Kabul');

// Count total districts
$totalDistricts = Afghanistan::countDistricts();
```

### Village Queries

#### List All Villages

```php
// Get all villages (collection of 10,363 villages)
$allVillages = Afghanistan::villages();

// Count villages
$totalVillages = Afghanistan::countVillages(); // 10363

// Iterate through villages
foreach ($allVillages as $village) {
    echo $village->name;
    echo $village->province;
    echo $village->district;
}
```

#### Villages by Province

```php
// Get villages in a specific province
$kabul = Afghanistan::provinceByName('Kabul');
$kabulVillages = Afghanistan::villagesByProvince($kabul->id);

// Get count of villages in a province
$villageCount = $kabulVillages->count();

// Map villages to array
$villageArray = $kabulVillages->map->toArray();
```

#### Villages by District (Recommended)

```php
// Get villages in a specific district
$kabul = Afghanistan::provinceByName('Kabul');
$kabulDistricts = Afghanistan::districts($kabul->id);
$kabulDistrict = $kabulDistricts->firstWhere('name', 'Kabul');

$kabulDistrictVillages = Afghanistan::villagesByDistrict($kabulDistrict->id);

foreach ($kabulDistrictVillages as $village) {
    echo $village->id;
    echo $village->name;
    echo $village->latitude;
    echo $village->longitude;
}
```

#### Villages by Province and District Name

```php
// Direct query by province and district names
$villages = Afghanistan::villages('Kabul', 'Kabul');
$villages = Afghanistan::villages('Herat', 'Herat');
$villages = Afghanistan::villages('Badakhshan', 'Wakhan');
```

#### Find Individual Villages

```php
// Get village by ID
$village = Afghanistan::village(4183);

// Get village by name
$village = Afghanistan::villageByName('Ab Bala');

// Access village properties
echo $village->id;        // 4183
echo $village->name;      // Ab Bala
echo $village->province;  // Bamyan
echo $village->district;  // Panjab
echo $village->latitude;  // 34.5
echo $village->longitude; // 68.5
```

## Advanced Usage

### 3-Level Cascade (Province → District → Village)

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// 1. Display provinces dropdown
$provinces = Afghanistan::provinces();
foreach ($provinces as $province) {
    // <option value="1">Kabul</option>
}

// 2. When user selects province, load districts
$selectedProvinceId = request('province_id');
$districts = Afghanistan::districts($selectedProvinceId);
foreach ($districts as $district) {
    // <option value="129">Kabul</option>
}

// 3. When user selects district, load villages
$selectedDistrictId = request('district_id');
$villages = Afghanistan::villagesByDistrict($selectedDistrictId);
foreach ($villages as $village) {
    // <option value="4183">Ab Bala</option>
}
```

### Blade Template - Dynamic Dropdowns

```blade
{{-- Province Dropdown --}}
<select name="province_id" id="province">
    <option value="">Select Province</option>
    @foreach (Afghanistan::provinces() as $province)
        <option value="{{ $province->id }}">{{ $province->name }}</option>
    @endforeach
</select>

{{-- District Dropdown (loads via AJAX) --}}
<select name="district_id" id="district">
    <option value="">Select District</option>
</select>

{{-- Village Dropdown (loads via AJAX) --}}
<select name="village_id" id="village">
    <option value="">Select Village</option>
</select>

<script>
document.getElementById('province').addEventListener('change', function() {
    const provinceId = this.value;
    fetch(`/api/districts/${provinceId}`)
        .then(response => response.json())
        .then(data => {
            // Populate district dropdown
        });
});

document.getElementById('district').addEventListener('change', function() {
    const districtId = this.value;
    fetch(`/api/villages/${districtId}`)
        .then(response => response.json())
        .then(data => {
            // Populate village dropdown
        });
});
</script>
```

### API Routes

```php
// routes/api.php

use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LocationController extends Controller
{
    // GET /api/provinces
    public function provinces()
    {
        return response()->json(
            Afghanistan::provinces()->map->toArray()
        );
    }

    // GET /api/districts/{provinceId}
    public function districts($provinceId)
    {
        return response()->json(
            Afghanistan::districts($provinceId)->map->toArray()
        );
    }

    // GET /api/villages?district_id=129
    public function villages(Request $request)
    {
        if ($request->filled('district_id')) {
            return response()->json(
                Afghanistan::villagesByDistrict(
                    (int) $request->query('district_id')
                )->map->toArray()
            );
        }

        if ($request->filled('province_id')) {
            return response()->json(
                Afghanistan::villagesByProvince(
                    (int) $request->query('province_id')
                )->map->toArray()
            );
        }

        return response()->json(
            Afghanistan::villages()->map->toArray()
        );
    }

    // GET /api/village/{id}
    public function village($id)
    {
        $village = Afghanistan::village($id);
        return response()->json($village->toArray());
    }
}

// In your routes file:
Route::apiResource('locations', LocationController::class);
Route::get('/api/provinces', [LocationController::class, 'provinces']);
Route::get('/api/districts/{provinceId}', [LocationController::class, 'districts']);
Route::get('/api/villages', [LocationController::class, 'villages']);
Route::get('/api/village/{id}', [LocationController::class, 'village']);
```

### Dependency Injection

```php
use Barialay\AfghanistanProvinceDistrictVillage\Afghanistan;
use Illuminate\Routing\Controller;

class LocationController extends Controller
{
    protected $afghanistan;

    public function __construct(Afghanistan $afghanistan)
    {
        $this->afghanistan = $afghanistan;
    }

    public function villagesByDistrict($districtId)
    {
        return $this->afghanistan
            ->villagesByDistrict($districtId)
            ->map->toArray();
    }

    public function getProvinces()
    {
        return $this->afghanistan->provinces();
    }
}
```

## Multilingual Support

### Localized Display

```php
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

// Default locale is Dari (fa)

// Get provinces with Dari labels
$provincesLocalized = Afghanistan::provincesLocalized();
// Example: ولایت: میدان وردک

// Get provinces with English labels
$provincesEnglish = Afghanistan::provincesLocalized('en');
// Example: Province: Maidan Wardak

// Get provinces with Pashto labels
$provincesPashto = Afghanistan::provincesLocalized('pa');

// Localized districts
$districtsLocalized = Afghanistan::districtsLocalized($provinceId);
// Example: ولسوالی: سیدآباد

// Localized villages
$villagesLocalized = Afghanistan::villagesByDistrictLocalized($districtId);
// Example: ولایت: میدان وردک: ولسوالی: سیدآباد: کلی VillageName
```

### Accessing Localized Data

```php
$province = Afghanistan::province(1);

// Get name in different languages
echo $province->name;    // English name
echo $province->name_fa; // Dari name
echo $province->name_pa; // Pashto name
```

## Data Structure Reference

### Province Object

```php
$province = Afghanistan::province(1);

// Properties
$province->id;        // int: 1
$province->name;      // string: Kabul
$province->name_fa;   // string: کابل
$province->name_pa;   // string: کابل
$province->latitude;  // float: 34.5553
$province->longitude; // float: 69.2075
```

### District Object

```php
$district = Afghanistan::districts(1)->first();

// Properties
$district->id;        // int: 1
$district->name;      // string: Kabul
$district->name_fa;   // string: کابل
$district->name_pa;   // string: کابل
$district->latitude;  // float: 34.5553
$district->longitude; // float: 69.2075
```

### Village Object

```php
$village = Afghanistan::village(4183);

// Properties
$village->id;                 // int: 4183
$village->name;               // string: Ab Bala
$village->province;           // string: Bamyan
$village->district;           // string: Panjab
$village->province_id;        // int: 7
$village->district_id;        // int: 129
$village->latitude;           // float: 34.5
$village->longitude;          // float: 68.5
$village->area_square_meters; // float: 1234567.89
$village->hectares;           // float: 123.45
```

## Configuration

### Publish Configuration

```bash
php artisan vendor:publish --tag=afghanistan-province-district-village-config
```

### Config File

```php
// config/afghanistan-province-district-village.php

return [
    'villages_file' => resource_path('afghanistan-province-district-village/villages.json'),
    'provinces_file' => resource_path('afghanistan-province-district-village/provinces-and-districts.json'),
    'default_locale' => 'en', // en, fa (Dari), pa (Pashto)
];
```

## Testing

```bash
# Run tests
composer test

# Run specific test
composer test -- tests/VillageTest.php
```

### Example Test

```php
<?php

namespace Tests;

use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

class LocationTest extends TestCase
{
    public function test_can_get_all_provinces()
    {
        $provinces = Afghanistan::provinces();
        
        $this->assertCount(34, $provinces);
    }

    public function test_can_get_village_by_id()
    {
        $village = Afghanistan::village(4183);
        
        $this->assertEquals('Ab Bala', $village->name);
        $this->assertEquals('Bamyan', $village->province);
    }

    public function test_can_get_villages_by_district()
    {
        $villages = Afghanistan::villagesByDistrict(129);
        
        $this->assertGreaterThan(0, $villages->count());
    }
}
```

## Performance Tips

- **Cache results** for frequently accessed data:
```php
$provinces = cache()->remember('provinces', 3600, function () {
    return Afghanistan::provinces();
});
```

- **Use pagination** for large result sets:
```php
$villages = Afghanistan::villages()->paginate(50);
```

- **Filter before converting to array**:
```php
$villages = Afghanistan::villagesByDistrict($districtId)
    ->where('name', 'like', 'Ab%')
    ->map->toArray();
```

## Common Issues & Solutions

### Issue: Village names appear in English only
**Solution:** Village names in the dataset are primarily in English/Latin script. Provinces and districts have full multilingual support.

### Issue: Missing GPS coordinates
**Solution:** Some villages may not have complete GPS data. Always check if `latitude` and `longitude` exist before using them.

### Issue: Performance degradation with large queries
**Solution:** Use filtering, pagination, or caching for better performance with large datasets.

## Troubleshooting

### Verify Installation

```bash
php artisan tinker

# In Tinker:
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;

Afghanistan::countProvinces();    // Should return: 34
Afghanistan::countVillages();     // Should return: 10363
Afghanistan::provinces()->first(); // Should return first province object
```

### Check Data Integrity

```bash
php artisan tinker

# Verify all provinces have villages
collect(Afghanistan::provinces())->each(function($province) {
    $count = Afghanistan::villagesByProvince($province->id)->count();
    echo "{$province->name}: {$count} villages\n";
});
```

## Additional Resources

- [GitHub Repository](https://github.com/barialay/afghanistan-province-district-village)
- [Contributing Guide](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)
- [License](LICENSE)

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/barialay/afghanistan-province-district-village).
