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
        public int $id,
        public string $name,
        public string $province,
        public string $district,
        public ?int $provinceId = null,
        public ?int $districtId = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?float $areaSquareMeters = null,
        public ?float $hectares = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
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
