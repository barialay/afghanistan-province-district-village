<?php

namespace Tests\Unit;

use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;
use PHPUnit\Framework\TestCase;

class VillageTest extends TestCase
{
    /**
     * Test that we can retrieve all villages
     */
    public function test_get_all_villages()
    {
        $villages = Afghanistan::villages();
        
        $this->assertNotEmpty($villages);
        $this->assertEquals(10363, $villages->count());
    }

    /**
     * Test village count
     */
    public function test_count_villages()
    {
        $count = Afghanistan::countVillages();
        
        $this->assertEquals(10363, $count);
    }

    /**
     * Test that we can get villages by province
     */
    public function test_get_villages_by_province()
    {
        $kabuProvinceId = 1;
        $villages = Afghanistan::villagesByProvince($kabuProvinceId);
        
        $this->assertNotEmpty($villages);
        $this->assertGreaterThan(0, $villages->count());
    }

    /**
     * Test that we can get villages by district
     */
    public function test_get_villages_by_district()
    {
        $districtId = 1;
        $villages = Afghanistan::villagesByDistrict($districtId);
        
        $this->assertNotEmpty($villages);
        $this->assertGreaterThan(0, $villages->count());
    }

    /**
     * Test that we can get village by ID
     */
    public function test_get_village_by_id()
    {
        $village = Afghanistan::village(1);
        
        $this->assertNotNull($village);
        $this->assertEquals(1, $village->id);
    }

    /**
     * Test that we can get village by name
     */
    public function test_get_village_by_name()
    {
        $village = Afghanistan::villageByName('Ab Bala');
        
        $this->assertNotNull($village);
        $this->assertEquals('Ab Bala', $village->name);
    }

    /**
     * Test village structure and properties
     */
    public function test_village_has_correct_structure()
    {
        $village = Afghanistan::village(1);
        
        $this->assertNotNull($village->id);
        $this->assertNotNull($village->name);
        $this->assertNotNull($village->province);
        $this->assertNotNull($village->district);
        $this->assertNotNull($village->province_id);
        $this->assertNotNull($village->district_id);
    }

    /**
     * Test village GPS coordinates
     */
    public function test_village_has_coordinates()
    {
        $village = Afghanistan::village(1);
        
        $this->assertIsFloat($village->latitude);
        $this->assertIsFloat($village->longitude);
    }

    /**
     * Test village can be converted to array
     */
    public function test_village_to_array()
    {
        $village = Afghanistan::village(1);
        $array = $village->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('province', $array);
        $this->assertArrayHasKey('district', $array);
    }

    /**
     * Test get villages by province and district name
     */
    public function test_get_villages_by_province_and_district_name()
    {
        $villages = Afghanistan::villages('Kabul', 'Kabul');
        
        $this->assertNotEmpty($villages);
        $this->assertGreaterThan(0, $villages->count());
    }

    /**
     * Test all provinces have villages
     */
    public function test_all_provinces_have_villages()
    {
        $provinces = Afghanistan::provinces();
        
        foreach ($provinces as $province) {
            $villages = Afghanistan::villagesByProvince($province->id);
            $this->assertGreaterThan(0, $villages->count(), 
                "Province {$province->name} has no villages");
        }
    }
}
