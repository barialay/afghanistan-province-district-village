<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Data;

use Barialay\AfghanistanProvinceDistrictVillage\Support\LocationFormatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class Village implements Arrayable, Jsonable, JsonSerializable
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string|null */
    public $nameFa;

    /** @var string */
    public $province;

    /** @var string */
    public $district;

    /** @var int|null */
    public $provinceId;

    /** @var int|null */
    public $districtId;

    /** @var float|null */
    public $latitude;

    /** @var float|null */
    public $longitude;

    /** @var float|null */
    public $areaSquareMeters;

    /** @var float|null */
    public $hectares;

    public function __construct(
        int $id,
        string $name,
        string $province,
        string $district,
        $provinceId = null,
        $districtId = null,
        $latitude = null,
        $longitude = null,
        $areaSquareMeters = null,
        $hectares = null,
        $nameFa = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->nameFa = $nameFa;
        $this->province = $province;
        $this->district = $district;
        $this->provinceId = $provinceId;
        $this->districtId = $districtId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->areaSquareMeters = $areaSquareMeters;
        $this->hectares = $hectares;
    }

    public function nameFor(string $locale = 'en'): string
    {
        if ($locale === 'fa' && $this->nameFa !== null && $this->nameFa !== '') {
            return $this->nameFa;
        }

        return $this->name;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_fa' => $this->nameFa,
            'province' => $this->province,
            'district' => $this->district,
            'province_id' => $this->provinceId,
            'district_id' => $this->districtId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'area_square_meters' => $this->areaSquareMeters,
            'hectares' => $this->hectares,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toLocalizedArray(
        string $locale = 'fa',
        ?Province $province = null,
        ?District $district = null
    ): array {
        $provinceName = $province !== null ? $province->nameFor($locale) : $this->province;
        $districtName = $district !== null ? $district->nameFor($locale) : $this->district;

        return array_merge($this->toArray(), [
            'name' => $this->nameFor($locale),
            'province' => $provinceName,
            'district' => $districtName,
            'label' => LocationFormatter::villageLabel($province, $district, $this, $locale),
            'display' => LocationFormatter::villageLabel($province, $district, $this, $locale),
        ]);
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
