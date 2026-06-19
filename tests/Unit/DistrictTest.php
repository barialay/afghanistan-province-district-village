<?php

namespace Tests\Unit;

use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;
use PHPUnit\Framework\TestCase;

class DistrictTest extends TestCase
{
    /**
     * Test that we can retrieve all districts
     */
    public function test_get_all_districts()
    {
        $districts = Afghanistan::districts();
        
        $this->assertNotEmpty($districts);
        $this->assertGreaterThan(400, $districts->count());
    }

    /**
     * Test that we can get districts by province
     */
    public function test_get_districts_by_province()
    {
        $kabuProvinceId = 1;
        $districts = Afghanistan::districts($kabuProvinceId);
        
        $this->assertNotEmpty($districts);
        $this->assertGreaterThan(0, $districts->count());
    }

    /**
     * Test that district has correct structure
     */
    public function test_district_has_correct_structure()
    {
        $districts = Afghanistan::districts(1);
        $district = $districts->first();
        
        $this->assertNotNull($district->id);
        $this->assertNotNull($district->name);
        $this->assertNotNull($district->name_fa);
        $this->assertNotNull($district->name_pa);
    }

    /**
     * Test that district has GPS coordinates
     */
    public function test_district_has_coordinates()
    {
        $districts = Afghanistan::districts(1);
        $district = $districts->first();
        
        $this->assertIsFloat($district->latitude);
        $this->assertIsFloat($district->longitude);
    }

    /**
     * Test finding district by name within a province
     */
    public function test_find_district_by_name()
    {
        $districts = Afghanistan::districts(1);
        $district = $districts->firstWhere('name', 'Kabul');
        
        $this->assertNotNull($district);
        $this->assertEquals('Kabul', $district->name);
    }
}
