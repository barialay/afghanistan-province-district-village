<?php

namespace Tests\Unit;

use Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan;
use PHPUnit\Framework\TestCase;

class ProvinceTest extends TestCase
{
    /**
     * Test that we can retrieve all provinces
     */
    public function test_get_all_provinces()
    {
        $provinces = Afghanistan::provinces();
        
        $this->assertNotEmpty($provinces);
        $this->assertCount(34, $provinces);
    }

    /**
     * Test that we can get a province by ID
     */
    public function test_get_province_by_id()
    {
        $province = Afghanistan::province(1);
        
        $this->assertNotNull($province);
        $this->assertEquals(1, $province->id);
        $this->assertEquals('Kabul', $province->name);
    }

    /**
     * Test that we can get a province by name
     */
    public function test_get_province_by_name()
    {
        $province = Afghanistan::provinceByName('Kabul');
        
        $this->assertNotNull($province);
        $this->assertEquals('Kabul', $province->name);
        $this->assertEquals(1, $province->id);
    }

    /**
     * Test that province has correct multilingual names
     */
    public function test_province_has_multilingual_names()
    {
        $province = Afghanistan::provinceByName('Kabul');
        
        $this->assertNotNull($province->name_fa);
        $this->assertNotNull($province->name_pa);
        $this->assertNotEmpty($province->name_fa);
        $this->assertNotEmpty($province->name_pa);
    }

    /**
     * Test that province has GPS coordinates
     */
    public function test_province_has_coordinates()
    {
        $province = Afghanistan::provinceByName('Kabul');
        
        $this->assertNotNull($province->latitude);
        $this->assertNotNull($province->longitude);
        $this->assertIsFloat($province->latitude);
        $this->assertIsFloat($province->longitude);
    }

    /**
     * Test province count
     */
    public function test_count_provinces()
    {
        $count = Afghanistan::countProvinces();
        
        $this->assertEquals(34, $count);
    }
}
