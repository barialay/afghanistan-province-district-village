<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Support;

class ProvinceNameMapper
{
    /** @var array<string, string> */
    private const ADMIN_TO_VILLAGE = [
        'Daikundi' => 'Daykundi',
        'Helmand' => 'Hilmand',
        'Herat' => 'Hirat',
        'Joz jan' => 'Jawzjan',
        'Kundoz' => 'Kunduz',
        'Urozgan' => 'Uruzgan',
        'Wardak' => 'Maydan Wardak',
        'Sar e pul' => 'Sari Pul',
    ];

    public static function villageNameForAdmin(string $adminProvinceName): string
    {
        return self::ADMIN_TO_VILLAGE[$adminProvinceName] ?? $adminProvinceName;
    }

    public static function matches(string $left, string $right): bool
    {
        return self::normalize($left) === self::normalize($right);
    }

    public static function villageMatchesAdmin(string $villageProvinceName, string $adminProvinceName): bool
    {
        if (self::matches($villageProvinceName, $adminProvinceName)) {
            return true;
        }

        return self::matches(
            $villageProvinceName,
            self::villageNameForAdmin($adminProvinceName)
        );
    }

    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }
}
