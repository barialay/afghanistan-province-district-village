<?php

namespace Barialay\AfghanistanProvinceDistrictVillage;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Illuminate\Support\Collection;

class Afghanistan
{
    private LocationRepositoryInterface $repository;

    private string $defaultLocale;

    public function __construct(
        LocationRepositoryInterface $repository,
        string $defaultLocale = 'en'
    ) {
        $this->repository = $repository;
        $this->defaultLocale = $defaultLocale;
    }

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

    /**
     * @return Collection<int, Village>
     */
    public function villagesByProvince(int $provinceId): Collection
    {
        return $this->repository->villagesByProvince($provinceId);
    }

    /**
     * @return Collection<int, Village>
     */
    public function villagesByDistrict(int $districtId): Collection
    {
        return $this->repository->villagesByDistrict($districtId);
    }

    public function village(int $id): ?Village
    {
        return $this->repository->findVillage($id);
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
