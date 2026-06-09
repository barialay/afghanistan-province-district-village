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

    public function findVillage(string $osmId): ?Village;

    public function findVillageByName(string $name): ?Village;
}
