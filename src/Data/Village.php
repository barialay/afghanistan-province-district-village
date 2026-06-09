<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class Village implements Arrayable, Jsonable, JsonSerializable
{
    public function __construct(
        public string $osmId,
        public string $name,
        public ?string $nameEn = null,
        public ?string $nameFa = null,
        public ?string $namePs = null,
        public ?string $province = null,
        public ?string $district = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $population = null,
    ) {}

    public function nameFor(string $locale = 'en'): string
    {
        return match ($locale) {
            'fa' => $this->nameFa ?? $this->name,
            'pa' => $this->namePs ?? $this->name,
            'en' => $this->nameEn ?? $this->name,
            default => $this->name,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'osm_id' => $this->osmId,
            'name' => $this->name,
            'name_en' => $this->nameEn,
            'name_fa' => $this->nameFa,
            'name_ps' => $this->namePs,
            'province' => $this->province,
            'district' => $this->district,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'population' => $this->population,
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
