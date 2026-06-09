<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Contracts;

use Barialay\AfghanistanProvinceDistrictVillage\Data\District;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Province;
use Barialay\AfghanistanProvinceDistrictVillage\Data\Village;
use Illuminate\Support\Collection;

interface LocationRepositoryInterface
{
    /**
     * @return Collection<int, Province>
     */
    public function provinces(): Collection;

    public function findProvince(int $id): ?Province;

    public function findProvinceByName(string $name): ?Province;

    /**
     * @return Collection<int, District>
     */
    public function districts(?int $provinceId = null): Collection;

    public function findDistrict(int $id): ?District;

    /**
     * @return Collection<int, Village>
     */
    public function villages(?string $province = null, ?string $district = null): Collection;

    /**
     * @return Collection<int, Village>
     */
    public function villagesByProvince(int $provinceId): Collection;

    /**
     * @return Collection<int, Village>
     */
    public function villagesByDistrict(int $districtId): Collection;

    public function findVillage(int $id): ?Village;

    public function findVillageByName(string $name): ?Village;
}
