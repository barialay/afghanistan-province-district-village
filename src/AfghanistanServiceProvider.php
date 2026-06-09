<?php

namespace Barialay\AfghanistanProvinceDistrictVillage;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Repositories\GeoJsonLocationRepository;
use Illuminate\Support\ServiceProvider;

class AfghanistanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/afghanistan-province-district-village.php',
            'afghanistan-province-district-village'
        );

        $this->app->singleton(LocationRepositoryInterface::class, function (): GeoJsonLocationRepository {
            return new GeoJsonLocationRepository(
                geojsonFile: (string) config('afghanistan-province-district-village.geojson_file'),
                provincesFile: (string) config('afghanistan-province-district-village.provinces_file'),
            );
        });

        $this->app->singleton(Afghanistan::class, function ($app): Afghanistan {
            return new Afghanistan(
                repository: $app->make(LocationRepositoryInterface::class),
                defaultLocale: (string) config('afghanistan-province-district-village.default_locale', 'en'),
            );
        });

        $this->app->alias(Afghanistan::class, 'afghanistan');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/afghanistan-province-district-village.php' => config_path('afghanistan-province-district-village.php'),
            ], 'afghanistan-province-district-village-config');

            $this->publishes([
                __DIR__.'/../resources/data' => resource_path('afghanistan-province-district-village'),
            ], 'afghanistan-province-district-village-data');
        }
    }
}
