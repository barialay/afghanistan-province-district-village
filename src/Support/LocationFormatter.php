<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Support;

use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;

class LocationFormatter
{
    public static function provinceLabel(Province $province, string $locale = 'fa'): string
    {
        return LocationLabels::for('province', $locale).': '.$province->nameFor($locale);
    }

    public static function districtLabel(District $district, string $locale = 'fa'): string
    {
        return LocationLabels::for('district', $locale).': '.$district->nameFor($locale);
    }

    public static function villageLabel(
        ?Province $province,
        ?District $district,
        Village $village,
        string $locale = 'fa'
    ): string {
        $parts = [];

        if ($province !== null) {
            $parts[] = self::provinceLabel($province, $locale);
        }

        if ($district !== null) {
            $parts[] = self::districtLabel($district, $locale);
        }

        $parts[] = LocationLabels::for('village', $locale).' '.$village->nameFor($locale);

        return implode(': ', $parts);
    }
}
