<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
class District implements Arrayable, Jsonable, JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $nameFa,
        public string $namePa,
        public ?float $latitude,
        public ?float $longitude,
        public int $provinceId,
        public string $provinceName,
    ) {}

    public function nameFor(string $locale = 'en'): string
    {
        return match ($locale) {
            'fa' => $this->nameFa,
            'pa' => $this->namePa,
            default => $this->name,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
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

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
