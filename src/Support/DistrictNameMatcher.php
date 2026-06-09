<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Support;

use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;

class DistrictNameMatcher
{
    /** @var array|null */
    private static $aliases;

    public static function matchDistrictId(Province $province, string $villageProvinceName, string $villageDistrictName): ?int
    {
        $aliasKey = $villageProvinceName.'|'.$villageDistrictName;
        $aliases = self::aliases();

        if (isset($aliases[$aliasKey])) {
            $aliasTarget = self::normalized($aliases[$aliasKey]);

            $district = $province->districts->first(function (District $district) use ($aliasTarget) {
                return self::normalized($district->name) === $aliasTarget;
            });

            if ($district instanceof District) {
                return $district->id;
            }
        }

        $searchVariants = self::variants($villageDistrictName);

        foreach ($province->districts as $district) {
            $districtVariants = self::variants($district->name, $district->nameFa, $district->namePa);

            foreach ($searchVariants as $searchVariant) {
                foreach ($districtVariants as $districtVariant) {
                    if (self::normalized($searchVariant) === self::normalized($districtVariant)) {
                        return $district->id;
                    }

                    if (self::compact($searchVariant) === self::compact($districtVariant)) {
                        return $district->id;
                    }
                }
            }
        }

        $bestDistrictId = null;
        $bestScore = 0.0;

        foreach ($province->districts as $district) {
            foreach (self::variants($district->name) as $districtVariant) {
                foreach ($searchVariants as $searchVariant) {
                    similar_text(self::compact($searchVariant), self::compact($districtVariant), $score);

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestDistrictId = $district->id;
                    }
                }
            }
        }

        if ($bestScore >= 82.0) {
            return $bestDistrictId;
        }

        return null;
    }

    /**
     * @return array
     */
    private static function aliases(): array
    {
        if (self::$aliases === null) {
            self::$aliases = require __DIR__.'/../Data/district-aliases.php';
        }

        return self::$aliases;
    }

    /**
     * @return array
     */
    private static function variants(): array
    {
        $names = func_get_args();
        $variants = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $variants[] = $name;

            if (preg_match('/^(.+?)\s*\((.+?)\)\s*$/u', $name, $matches)) {
                $variants[] = trim($matches[1]);
                $variants[] = trim($matches[2]);
            }
        }

        return array_values(array_unique($variants));
    }

    private static function normalized(string $value): string
    {
        return ProvinceNameMapper::normalize($value);
    }

    private static function compact(string $value): string
    {
        $normalized = self::normalized($value);
        $compact = preg_replace('/[^a-z0-9\x{0600}-\x{06FF}]/u', '', $normalized);

        return $compact !== null ? $compact : '';
    }
}
