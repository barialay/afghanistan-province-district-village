<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Support;

class LocationLabels
{
    /** @var array<string, array<string, string>> */
    private static $labels = [
        'fa' => [
            'province' => 'ولایت',
            'district' => 'ولسوالی',
            'village' => 'کلی',
        ],
        'pa' => [
            'province' => 'ولایت',
            'district' => 'ولسوالۍ',
            'village' => 'کلي',
        ],
        'en' => [
            'province' => 'Province',
            'district' => 'District',
            'village' => 'Village',
        ],
    ];

    public static function for(string $type, string $locale = 'fa'): string
    {
        if (! isset(self::$labels[$locale])) {
            $locale = 'en';
        }

        return self::$labels[$locale][$type] ?? self::$labels['en'][$type];
    }
}
