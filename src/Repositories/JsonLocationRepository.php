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
    /** @var Collection|null */
    private $provinces;

    /** @var Collection|null */
    private $villages;

    /** @var string */
    private $villagesFile;

    /** @var string */
    private $provincesFile;

    public function __construct(string $villagesFile, string $provincesFile)
    {
        $this->villagesFile = $villagesFile;
        $this->provincesFile = $provincesFile;
    }

    public function provinces(): Collection
    {
        return $this->loadProvinces()
            ->filter(function (Province $province) {
                return $province->name !== 'N/A';
            })
            ->values();
    }

    public function findProvince(int $id): ?Province
    {
        return $this->provinces()->firstWhere('id', $id);
    }

    public function findProvinceByName(string $name): ?Province
    {
        $normalized = ProvinceNameMapper::normalize($name);

        return $this->provinces()->first(function (Province $province) use ($normalized) {
            return ProvinceNameMapper::normalize($province->name) === $normalized
                || ProvinceNameMapper::normalize($province->nameFa) === $normalized
                || ProvinceNameMapper::normalize($province->namePa) === $normalized;
        });
    }

    public function districts(?int $provinceId = null): Collection
    {
        $districts = $this->loadProvinces()->flatMap(function (Province $province) {
            return $province->districts;
        });

        if ($provinceId === null) {
            return $districts->values();
        }

        return $districts
            ->filter(function (District $district) use ($provinceId) {
                return $district->provinceId === $provinceId;
            })
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
                $villages = $villages->filter(function (Village $village) use ($provinceRecord) {
                    return $village->provinceId === $provinceRecord->id;
                });
            } else {
                $normalizedProvince = ProvinceNameMapper::normalize($province);

                $villages = $villages->filter(function (Village $village) use ($normalizedProvince) {
                    return ProvinceNameMapper::normalize($village->province) === $normalizedProvince;
                });
            }
        }

        if ($district !== null) {
            $districtRecord = $this->districts()->first(function (District $item) use ($district) {
                return ProvinceNameMapper::normalize($item->name) === ProvinceNameMapper::normalize($district)
                    || ProvinceNameMapper::normalize($item->nameFa) === ProvinceNameMapper::normalize($district)
                    || ProvinceNameMapper::normalize($item->namePa) === ProvinceNameMapper::normalize($district);
            });

            if ($districtRecord !== null) {
                $villages = $villages->filter(function (Village $village) use ($districtRecord) {
                    return $village->districtId === $districtRecord->id;
                });
            } else {
                $normalizedDistrict = ProvinceNameMapper::normalize($district);

                $villages = $villages->filter(function (Village $village) use ($normalizedDistrict) {
                    return ProvinceNameMapper::normalize($village->district) === $normalizedDistrict;
                });
            }
        }

        return $villages->values();
    }

    public function villagesByProvince(int $provinceId): Collection
    {
        return $this->loadVillages()
            ->filter(function (Village $village) use ($provinceId) {
                return $village->provinceId === $provinceId;
            })
            ->values();
    }

    public function villagesByDistrict(int $districtId): Collection
    {
        return $this->loadVillages()
            ->filter(function (Village $village) use ($districtId) {
                return $village->districtId === $districtId;
            })
            ->values();
    }

    public function findVillage(int $id): ?Village
    {
        return $this->loadVillages()->firstWhere('id', $id);
    }

    public function findVillageByName(string $name): ?Village
    {
        $normalized = ProvinceNameMapper::normalize($name);

        return $this->loadVillages()->first(function (Village $village) use ($normalized) {
            return ProvinceNameMapper::normalize($village->name) === $normalized;
        });
    }

    /**
     * @return Collection
     */
    private function loadProvinces(): Collection
    {
        if ($this->provinces !== null) {
            return $this->provinces;
        }

        $data = $this->readJson($this->provincesFile);
        $repository = $this;

        $this->provinces = collect($data)->map(function (array $item) use ($repository) {
            $districts = collect($item['districts'] ?? [])->map(function (array $district) use ($item, $repository) {
                return new District(
                    (int) $district['id'],
                    trim((string) $district['name']),
                    trim((string) ($district['nameFa'] ?? $district['name'])),
                    trim((string) ($district['namePa'] ?? $district['name'])),
                    $repository->toFloat($district['latitude'] ?? null),
                    $repository->toFloat($district['longitude'] ?? null),
                    (int) $item['id'],
                    (string) $item['name']
                );
            });

            return new Province(
                (int) $item['id'],
                trim((string) $item['name']),
                trim((string) ($item['nameFa'] ?? $item['name'])),
                trim((string) ($item['namePa'] ?? $item['name'])),
                $repository->toFloat($item['latitude'] ?? null),
                $repository->toFloat($item['longitude'] ?? null),
                $districts
            );
        })->values();

        return $this->provinces;
    }

    /**
     * @return Collection
     */
    private function loadVillages(): Collection
    {
        if ($this->villages !== null) {
            return $this->villages;
        }

        $data = $this->readJson($this->villagesFile);
        $provinces = $this->loadProvinces();
        $repository = $this;

        $this->villages = collect($data)->map(function (array $item) use ($provinces, $repository) {
            $villageProvince = (string) ($item['Province'] ?? $item['province'] ?? '');
            $villageDistrict = (string) ($item['District'] ?? $item['district'] ?? '');

            $province = $provinces->first(function (Province $record) use ($villageProvince) {
                return ProvinceNameMapper::villageMatchesAdmin($villageProvince, $record->name);
            });

            $districtId = null;
            $provinceId = null;

            if ($province !== null) {
                $provinceId = $province->id;
                $districtId = DistrictNameMatcher::matchDistrictId($province, $villageProvince, $villageDistrict);
            }

            return new Village(
                (int) ($item['No'] ?? $item['number'] ?? $item['id'] ?? 0),
                (string) ($item['Village Name'] ?? $item['name'] ?? ''),
                $villageProvince,
                $villageDistrict,
                $provinceId,
                $districtId,
                $repository->toFloat($item['Latitude'] ?? $item['latitude'] ?? null),
                $repository->toFloat($item['Longitude'] ?? $item['longitude'] ?? null),
                $repository->toFloat($item['Area(Square Meter)'] ?? $item['area_square_meters'] ?? null),
                $repository->toFloat($item['Hectares'] ?? $item['hectares'] ?? null),
                isset($item['name_fa']) ? (string) $item['name_fa'] : null
            );
        })->values();

        return $this->villages;
    }

    /**
     * @return array
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

        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException("Invalid JSON structure in data file: {$path}");
        }

        return $data;
    }

    /**
     * @param  mixed  $value
     * @return float|null
     */
    private function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
