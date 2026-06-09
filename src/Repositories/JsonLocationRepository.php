<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Repositories;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Barialay\AfghanistanProvinceDistrictVillage\Support\DistrictNameMatcher;
use Barialay\AfghanistanProvinceDistrictVillage\Support\ProvinceNameMapper;
use Illuminate\Support\Collection;
use RuntimeException;

class JsonLocationRepository implements LocationRepositoryInterface
{
    /** @var Collection<int, Province>|null */
    private ?Collection $provinces = null;

    /** @var Collection<int, Village>|null */
    private ?Collection $villages = null;

    private string $villagesFile;

    private string $provincesFile;

    public function __construct(string $villagesFile, string $provincesFile)
    {
        $this->villagesFile = $villagesFile;
        $this->provincesFile = $provincesFile;
    }

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
        $normalized = ProvinceNameMapper::normalize($name);

        return $this->provinces()->first(
            fn (Province $province): bool => ProvinceNameMapper::normalize($province->name) === $normalized
                || ProvinceNameMapper::normalize($province->nameFa) === $normalized
                || ProvinceNameMapper::normalize($province->namePa) === $normalized
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
            $provinceRecord = $this->findProvinceByName($province);

            if ($provinceRecord !== null) {
                $villages = $villages->filter(
                    fn (Village $village): bool => $village->provinceId === $provinceRecord->id
                );
            } else {
                $normalizedProvince = ProvinceNameMapper::normalize($province);

                $villages = $villages->filter(
                    fn (Village $village): bool => ProvinceNameMapper::normalize($village->province) === $normalizedProvince
                );
            }
        }

        if ($district !== null) {
            $districtRecord = $this->districts()->first(
                fn (District $item): bool => ProvinceNameMapper::normalize($item->name) === ProvinceNameMapper::normalize($district)
                    || ProvinceNameMapper::normalize($item->nameFa) === ProvinceNameMapper::normalize($district)
                    || ProvinceNameMapper::normalize($item->namePa) === ProvinceNameMapper::normalize($district)
            );

            if ($districtRecord !== null) {
                $villages = $villages->filter(
                    fn (Village $village): bool => $village->districtId === $districtRecord->id
                );
            } else {
                $normalizedDistrict = ProvinceNameMapper::normalize($district);

                $villages = $villages->filter(
                    fn (Village $village): bool => ProvinceNameMapper::normalize($village->district) === $normalizedDistrict
                );
            }
        }

        return $villages->values();
    }

    public function villagesByProvince(int $provinceId): Collection
    {
        return $this->loadVillages()
            ->filter(fn (Village $village): bool => $village->provinceId === $provinceId)
            ->values();
    }

    public function villagesByDistrict(int $districtId): Collection
    {
        return $this->loadVillages()
            ->filter(fn (Village $village): bool => $village->districtId === $districtId)
            ->values();
    }

    public function findVillage(int $id): ?Village
    {
        return $this->loadVillages()->firstWhere('id', $id);
    }

    public function findVillageByName(string $name): ?Village
    {
        $normalized = ProvinceNameMapper::normalize($name);

        return $this->loadVillages()->first(
            fn (Village $village): bool => ProvinceNameMapper::normalize($village->name) === $normalized
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
                    name: trim((string) $district['name']),
                    nameFa: trim((string) ($district['nameFa'] ?? $district['name'])),
                    namePa: trim((string) ($district['namePa'] ?? $district['name'])),
                    latitude: $this->toFloat($district['latitude'] ?? null),
                    longitude: $this->toFloat($district['longitude'] ?? null),
                    provinceId: (int) $item['id'],
                    provinceName: (string) $item['name'],
                )
            );

            return new Province(
                id: (int) $item['id'],
                name: trim((string) $item['name']),
                nameFa: trim((string) ($item['nameFa'] ?? $item['name'])),
                namePa: trim((string) ($item['namePa'] ?? $item['name'])),
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

        $data = $this->readJson($this->villagesFile);
        $provinces = $this->loadProvinces();

        $this->villages = collect($data)->map(function (array $item) use ($provinces): Village {
            $villageProvince = (string) ($item['Province'] ?? $item['province'] ?? '');
            $villageDistrict = (string) ($item['District'] ?? $item['district'] ?? '');

            $province = $provinces->first(
                fn (Province $record): bool => ProvinceNameMapper::villageMatchesAdmin($villageProvince, $record->name)
            );

            $districtId = null;

            if ($province !== null) {
                $districtId = DistrictNameMatcher::matchDistrictId($province, $villageProvince, $villageDistrict);
            }

            return new Village(
                id: (int) ($item['No'] ?? $item['number'] ?? $item['id'] ?? 0),
                name: (string) ($item['Village Name'] ?? $item['name'] ?? ''),
                province: $villageProvince,
                district: $villageDistrict,
                provinceId: $province?->id,
                districtId: $districtId,
                latitude: $this->toFloat($item['Latitude'] ?? $item['latitude'] ?? null),
                longitude: $this->toFloat($item['Longitude'] ?? $item['longitude'] ?? null),
                areaSquareMeters: $this->toFloat($item['Area(Square Meter)'] ?? $item['area_square_meters'] ?? null),
                hectares: $this->toFloat($item['Hectares'] ?? $item['hectares'] ?? null),
            );
        })->values();

        return $this->villages;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readJson(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Afghanistan data file not found or not readable: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read Afghanistan data file: {$path}");
        }

        $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException("Invalid JSON structure in data file: {$path}");
        }

        return $data;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
