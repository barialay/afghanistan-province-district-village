<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Tests;

use Barialay\AfghanistanProvinceDistrictVillage\Afghanistan;
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan as AfghanistanFacade;

class LocationRepositoryTest extends TestCase
{
    public function test_it_loads_provinces_from_admin_data()
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $this->assertGreaterThanOrEqual(34, $afghanistan->countProvinces());
    }

    public function test_it_finds_a_province_by_english_or_dari_name()
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $kabul = $afghanistan->provinceByName('Kabul');
        $kabulDari = $afghanistan->provinceByName('کابل');

        $this->assertNotNull($kabul);
        $this->assertNotNull($kabulDari);
        $this->assertSame('Kabul', $kabul->name);
        $this->assertSame('Kabul', $kabulDari->name);
    }

    public function test_it_links_villages_across_all_provinces()
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $this->assertSame(8892, $afghanistan->countVillages());

        $linked = $afghanistan->villages()->filter(function ($village) {
            return $village->provinceId !== null && $village->districtId !== null;
        });

        $this->assertGreaterThan(8400, $linked->count());

        foreach ($afghanistan->provinces() as $province) {
            $this->assertGreaterThan(
                0,
                $afghanistan->villagesByProvince($province->id)->count(),
                "Expected villages for {$province->name}"
            );
        }
    }

    public function test_it_supports_province_district_village_cascade_for_multiple_provinces()
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $cases = [
            ['Kabul', 'Kabul', 33],
            ['Herat', 'Herat', 25],
            ['Ghazni', 'Andar', 25],
            ['Bamyan', 'Center of Bamyan', 25],
            ['Urozgan', 'Tarin Kowt', 49],
        ];

        foreach ($cases as $case) {
            $provinceName = $case[0];
            $districtName = $case[1];
            $minimum = $case[2];

            $province = $afghanistan->provinceByName($provinceName);
            $this->assertNotNull($province, "Province {$provinceName} should exist");

            $district = $afghanistan->districts($province->id)->firstWhere('name', $districtName);
            $this->assertNotNull($district, "District {$districtName} should exist in {$provinceName}");

            $this->assertGreaterThanOrEqual(
                $minimum,
                $afghanistan->villagesByDistrict($district->id)->count(),
                "Villages missing for {$provinceName} / {$districtName}"
            );
        }
    }

    public function test_it_finds_a_village_by_id_and_name()
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $village = $afghanistan->village(4183);

        $this->assertNotNull($village);
        $this->assertSame('Ab Bala', $village->name);
        $this->assertSame('Bamyan', $village->province);

        $this->assertNotNull($afghanistan->villageByName('Ab Bala'));
    }

    public function test_it_filters_districts_by_province()
    {
        $afghanistan = $this->app->make(Afghanistan::class);
        $province = $afghanistan->provinceByName('Kabul');

        $districts = $afghanistan->districts($province->id);

        $this->assertGreaterThan(0, $districts->count());
        $this->assertTrue($districts->every(function ($district) use ($province) {
            return $district->provinceId === $province->id;
        }));
    }

    public function test_facade_resolves_correctly()
    {
        $this->assertSame(8892, AfghanistanFacade::countVillages());
        $this->assertGreaterThanOrEqual(34, AfghanistanFacade::countProvinces());
    }

    public function test_it_formats_dari_labels_for_province_district_and_village()
    {
        $afghanistan = $this->app->make(Afghanistan::class);
        $province = $afghanistan->provinceByName('Wardak');
        $district = $afghanistan->districts($province->id)->firstWhere('name', 'Saydabad');

        $this->assertNotNull($province);
        $this->assertNotNull($district);

        $provinceLabel = $province->toLocalizedArray('fa');
        $this->assertSame('ولایت: میدان وردک', $provinceLabel['label']);
        $this->assertSame('میدان وردک', $provinceLabel['name']);

        $districtLabel = $district->toLocalizedArray('fa');
        $this->assertSame('ولسوالی: سیدآباد', $districtLabel['label']);
        $this->assertSame('سیدآباد', $districtLabel['name']);

        $villages = $afghanistan->villagesByDistrictLocalized($district->id, 'fa');
        $this->assertGreaterThan(0, $villages->count());

        $first = $villages->first();
        $this->assertStringStartsWith('ولایت: میدان وردک: ولسوالی: سیدآباد: کلی ', $first['display']);
    }
}
