<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Facades;

use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection<int, Province> provinces()
 * @method static Province|null province(int $id)
 * @method static Province|null provinceByName(string $name)
 * @method static Collection<int, District> districts(?int $provinceId = null)
 * @method static District|null district(int $id)
 * @method static Collection<int, Village> villages(?string $province = null, ?string $district = null)
 * @method static Collection<int, Village> villagesByProvince(int $provinceId)
 * @method static Collection<int, Village> villagesByDistrict(int $districtId)
 * @method static Village|null village(int $id)
 * @method static Village|null villageByName(string $name)
 * @method static string locale()
 * @method static int countProvinces()
 * @method static int countDistricts(?int $provinceId = null)
 * @method static int countVillages(?string $province = null, ?string $district = null)
 *
 * @see \Barialay\AfghanistanProvinceDistrictVillage\Afghanistan
 */
class Afghanistan extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'afghanistan';
    }
}
