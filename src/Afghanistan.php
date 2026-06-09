<?php

namespace Barialay\AfghanistanProvinceDistrictVillage;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Illuminate\Support\Collection;

class Afghanistan
{
    /** @var LocationRepositoryInterface */
    private $repository;

    /** @var string */
    private $defaultLocale;

    public function __construct(LocationRepositoryInterface $repository, string $defaultLocale = 'en')
    {
        $this->repository = $repository;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return Collection
     */
    public function provinces()
    {
        return $this->repository->provinces();
    }

    /**
     * @return Province|null
     */
    public function province(int $id)
    {
        return $this->repository->findProvince($id);
    }

    /**
     * @return Province|null
     */
    public function provinceByName(string $name)
    {
        return $this->repository->findProvinceByName($name);
    }

    /**
     * @return Collection
     */
    public function districts(?int $provinceId = null)
    {
        return $this->repository->districts($provinceId);
    }

    /**
     * @return District|null
     */
    public function district(int $id)
    {
        return $this->repository->findDistrict($id);
    }

    /**
     * @return Collection
     */
    public function villages(?string $province = null, ?string $district = null)
    {
        return $this->repository->villages($province, $district);
    }

    /**
     * @return Collection
     */
    public function villagesByProvince(int $provinceId)
    {
        return $this->repository->villagesByProvince($provinceId);
    }

    /**
     * @return Collection
     */
    public function villagesByDistrict(int $districtId)
    {
        return $this->repository->villagesByDistrict($districtId);
    }

    /**
     * @return Village|null
     */
    public function village(int $id)
    {
        return $this->repository->findVillage($id);
    }

    /**
     * @return Village|null
     */
    public function villageByName(string $name)
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
