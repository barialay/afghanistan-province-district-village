<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Support;

class OsmNameNormalizer
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(
            ['ū', 'ū', 'ā', 'ī', 'ē', 'ō', 'ḫ', 'ḏ', 'ṯ', 'ẕ', 'ḍ', 'ṭ', 'ṣ', 'ḡ', 'š', 'ç', '’', "'", '-', '_'],
            ['u', 'u', 'a', 'i', 'e', 'o', 'h', 'd', 't', 'z', 'd', 't', 's', 'g', 's', 'c', '', '', ' ', ' '],
            $value
        );
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    public static function hasArabicScript(string $value): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $value);
    }

    public static function isLatinLike(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return ! self::hasArabicScript($value);
    }

    public static function namesMatch(string $left, string $right): bool
    {
        return self::normalize($left) === self::normalize($right);
    }

    public static function namesSimilar(string $left, string $right): bool
    {
        $left = self::normalize($left);
        $right = self::normalize($right);

        if ($left === '' || $right === '') {
            return false;
        }

        if ($left === $right) {
            return true;
        }

        similar_text($left, $right, $percent);

        return $percent >= 88.0;
    }
}
