<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;

class Province implements Arrayable, Jsonable, JsonSerializable
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

    /** @var Collection */
    public $districts;

    /**
     * @param  Collection  $districts
     */
    public function __construct(
        int $id,
        string $name,
        string $nameFa,
        string $namePa,
        $latitude,
        $longitude,
        Collection $districts
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->nameFa = $nameFa;
        $this->namePa = $namePa;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->districts = $districts;
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
            'districts' => $this->districts->map->toArray()->values()->all(),
        ];
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
