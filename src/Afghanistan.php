<?php

namespace Barialay\AfghanistanProvinceDistrictVillage;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Illuminate\Support\Collection;

class Afghanistan
{
    public function __construct(
        private readonly LocationRepositoryInterface $repository,
        private readonly string $defaultLocale = 'en',
    ) {}

    /**
     * @return Collection<int, Province>
     */
    public function provinces(): Collection
    {
        return $this->repository->provinces();
    }

    public function province(int $id): ?Province
    {
        return $this->repository->findProvince($id);
    }

    public function provinceByName(string $name): ?Province
    {
        return $this->repository->findProvinceByName($name);
    }

    /**
     * @return Collection<int, District>
     */
    public function districts(?int $provinceId = null): Collection
    {
        return $this->repository->districts($provinceId);
    }

    public function district(int $id): ?District
    {
        return $this->repository->findDistrict($id);
    }

    /**
     * @return Collection<int, Village>
     */
    public function villages(?string $province = null, ?string $district = null): Collection
    {
        return $this->repository->villages($province, $district);
    }

    public function village(string $osmId): ?Village
    {
        return $this->repository->findVillage($osmId);
    }

    public function villageByName(string $name): ?Village
    {
        return $this->repository->findVillageByName($name);
    }

    public function locale(): string
    {
        return $this->defaultLocale;
    }

    public function countProvinces(): int
    {
        return $this->provinces()->count();
    }

    public function countDistricts(?int $provinceId = null): int
    {
        return $this->districts($provinceId)->count();
    }

    public function countVillages(?string $province = null, ?string $district = null): int
    {
        return $this->villages($province, $district)->count();
    }
}
