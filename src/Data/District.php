<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Data;

use Barialay\AfghanistanProvinceDistrictVillage\Support\LocationFormatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class District implements Arrayable, Jsonable, JsonSerializable
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $nameFa;

    /** @var string */
    public $namePa;

    /** @var float|null */
    public $latitude;

    /** @var float|null */
    public $longitude;

    /** @var int */
    public $provinceId;

    /** @var string */
    public $provinceName;

    public function __construct(
        int $id,
        string $name,
        string $nameFa,
        string $namePa,
        $latitude,
        $longitude,
        int $provinceId,
        string $provinceName
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->nameFa = $nameFa;
        $this->namePa = $namePa;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->provinceId = $provinceId;
        $this->provinceName = $provinceName;
    }

    public function nameFor(string $locale = 'en'): string
    {
        if ($locale === 'fa') {
            return $this->nameFa;
        }

        if ($locale === 'pa') {
            return $this->namePa;
        }

        return $this->name;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_fa' => $this->nameFa,
            'name_pa' => $this->namePa,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'province_id' => $this->provinceId,
            'province_name' => $this->provinceName,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toLocalizedArray(string $locale = 'fa'): array
    {
        return array_merge($this->toArray(), [
            'name' => $this->nameFor($locale),
            'label' => LocationFormatter::districtLabel($this, $locale),
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
