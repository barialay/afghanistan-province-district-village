<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Tests;

use Barialay\AfghanistanProvinceDistrictVillage\Afghanistan;
use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan as AfghanistanFacade;

class LocationRepositoryTest extends TestCase
{
    public function test_it_loads_provinces_from_admin_data(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $this->assertGreaterThanOrEqual(34, $afghanistan->countProvinces());
    }

    public function test_it_finds_a_province_by_name(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $province = $afghanistan->provinceByName('Kabul');

        $this->assertNotNull($province);
        $this->assertSame('Kabul', $province->name);
    }

    public function test_it_loads_villages_from_geojson(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $this->assertSame(2932, $afghanistan->countVillages());
    }

    public function test_it_finds_a_village_by_osm_id(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $village = $afghanistan->village('node/8378744476');

        $this->assertNotNull($village);
        $this->assertSame('کشت', $village->name);
        $this->assertSame('Kesht', $village->nameEn);
        $this->assertNotNull($village->latitude);
        $this->assertNotNull($village->longitude);
    }

    public function test_it_finds_a_village_by_name(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);

        $village = $afghanistan->villageByName('Kesht');

        $this->assertNotNull($village);
        $this->assertSame('node/8378744476', $village->osmId);
    }

    public function test_it_filters_districts_by_province(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);
        $province = $afghanistan->provinceByName('Kabul');

        $this->assertNotNull($province);

        $districts = $afghanistan->districts($province->id);

        $this->assertGreaterThan(0, $districts->count());
        $this->assertTrue($districts->every(fn ($district) => $district->provinceId === $province->id));
    }

    public function test_facade_resolves_correctly(): void
    {
        $this->assertSame(2932, AfghanistanFacade::countVillages());
        $this->assertGreaterThanOrEqual(34, AfghanistanFacade::countProvinces());
    }

    public function test_village_supports_localized_names(): void
    {
        $afghanistan = $this->app->make(Afghanistan::class);
        $village = $afghanistan->villageByName('Do Ab');

        $this->assertNotNull($village);
        $this->assertSame('Do Ab', $village->nameFor('en'));
        $this->assertSame('دوآب', $village->name);
    }
}
