<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Repositories;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Illuminate\Support\Collection;
use RuntimeException;

class GeoJsonLocationRepository implements LocationRepositoryInterface
{
    /** @var Collection<int, Province>|null */
    private ?Collection $provinces = null;

    /** @var Collection<int, Village>|null */
    private ?Collection $villages = null;

    public function __construct(
        private readonly string $geojsonFile,
        private readonly string $provincesFile,
    ) {}

    public function provinces(): Collection
    {
        return $this->loadProvinces()
            ->filter(fn (Province $province): bool => $province->name !== 'N/A')
            ->values();
    }

    public function findProvince(int $id): ?Province
    {
        return $this->provinces()->firstWhere('id', $id);
    }

    public function findProvinceByName(string $name): ?Province
    {
        $normalized = $this->normalize($name);

        return $this->provinces()->first(
            fn (Province $province): bool => $this->normalize($province->name) === $normalized
                || $this->normalize($province->nameFa) === $normalized
                || $this->normalize($province->namePa) === $normalized
        );
    }

    public function districts(?int $provinceId = null): Collection
    {
        $districts = $this->loadProvinces()
            ->flatMap(fn (Province $province): Collection => $province->districts);

        if ($provinceId === null) {
            return $districts->values();
        }

        return $districts
            ->filter(fn (District $district): bool => $district->provinceId === $provinceId)
            ->values();
    }

    public function findDistrict(int $id): ?District
    {
        return $this->districts()->firstWhere('id', $id);
    }

    public function villages(?string $province = null, ?string $district = null): Collection
    {
        $villages = $this->loadVillages();

        if ($province !== null) {
            $normalizedProvince = $this->normalize($province);

            $villages = $villages->filter(
                fn (Village $village): bool => $village->province !== null
                    && $this->normalize($village->province) === $normalizedProvince
            );
        }

        if ($district !== null) {
            $normalizedDistrict = $this->normalize($district);

            $villages = $villages->filter(
                fn (Village $village): bool => $village->district !== null
                    && $this->normalize($village->district) === $normalizedDistrict
            );
        }

        return $villages->values();
    }

    public function findVillage(string $osmId): ?Village
    {
        return $this->loadVillages()->firstWhere('osmId', $osmId);
    }

    public function findVillageByName(string $name): ?Village
    {
        $normalized = $this->normalize($name);

        return $this->loadVillages()->first(
            fn (Village $village): bool => $this->normalize($village->name) === $normalized
                || ($village->nameEn !== null && $this->normalize($village->nameEn) === $normalized)
                || ($village->nameFa !== null && $this->normalize($village->nameFa) === $normalized)
                || ($village->namePs !== null && $this->normalize($village->namePs) === $normalized)
        );
    }

    /**
     * @return Collection<int, Province>
     */
    private function loadProvinces(): Collection
    {
        if ($this->provinces !== null) {
            return $this->provinces;
        }

        $data = $this->readJson($this->provincesFile);

        $this->provinces = collect($data)->map(function (array $item): Province {
            $districts = collect($item['districts'] ?? [])->map(
                fn (array $district): District => new District(
                    id: (int) $district['id'],
                    name: (string) $district['name'],
                    nameFa: (string) ($district['nameFa'] ?? $district['name']),
                    namePa: (string) ($district['namePa'] ?? $district['name']),
                    latitude: $this->toFloat($district['latitude'] ?? null),
                    longitude: $this->toFloat($district['longitude'] ?? null),
                    provinceId: (int) $item['id'],
                    provinceName: (string) $item['name'],
                )
            );

            return new Province(
                id: (int) $item['id'],
                name: (string) $item['name'],
                nameFa: (string) ($item['nameFa'] ?? $item['name']),
                namePa: (string) ($item['namePa'] ?? $item['name']),
                latitude: $this->toFloat($item['latitude'] ?? null),
                longitude: $this->toFloat($item['longitude'] ?? null),
                districts: $districts,
            );
        })->values();

        return $this->provinces;
    }

    /**
     * @return Collection<int, Village>
     */
    private function loadVillages(): Collection
    {
        if ($this->villages !== null) {
            return $this->villages;
        }

        $data = $this->readGeoJson($this->geojsonFile);

        $this->villages = collect($data['features'] ?? [])->map(function (array $feature): Village {
            $properties = $feature['properties'] ?? [];
            [$latitude, $longitude] = $this->extractCoordinates($feature['geometry'] ?? []);

            return new Village(
                osmId: (string) ($feature['id'] ?? $properties['@id'] ?? ''),
                name: (string) ($properties['name'] ?? ''),
                nameEn: $this->nullableString($properties['name:en'] ?? null),
                nameFa: $this->nullableString($properties['name:fa'] ?? null),
                namePs: $this->nullableString($properties['name:ps'] ?? null),
                province: $this->nullableString(
                    $properties['addr:province'] ?? $properties['province'] ?? $properties['is_in:province'] ?? null
                ),
                district: $this->nullableString(
                    $properties['district'] ?? $properties['addr:district'] ?? $properties['addr:city'] ?? $properties['is_in:district'] ?? null
                ),
                latitude: $latitude,
                longitude: $longitude,
                population: $this->nullableString($properties['population'] ?? null),
            );
        })->values();

        return $this->villages;
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function extractCoordinates(array $geometry): array
    {
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? null;

        if (! is_array($coordinates)) {
            return [null, null];
        }

        if ($type === 'Point') {
            return [(float) $coordinates[1], (float) $coordinates[0]];
        }

        if ($type === 'Polygon' && isset($coordinates[0][0]) && is_array($coordinates[0][0])) {
            $point = $coordinates[0][0];

            return [(float) $point[1], (float) $point[0]];
        }

        return [null, null];
    }

    /**
     * @return array<string, mixed>
     */
    private function readGeoJson(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Afghanistan GeoJSON data file not found or not readable: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read Afghanistan GeoJSON data file: {$path}");
        }

        $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data) || ($data['type'] ?? null) !== 'FeatureCollection') {
            throw new RuntimeException("Invalid GeoJSON FeatureCollection in data file: {$path}");
        }

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readJson(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Afghanistan provinces data file not found or not readable: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read Afghanistan provinces data file: {$path}");
        }

        $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException("Invalid JSON structure in data file: {$path}");
        }

        return $data;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
