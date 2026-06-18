<?php

namespace Barialay\AfghanistanProvinceDistrictVillage;

use Barialay\AfghanistanProvinceDistrictVillage\Contracts\LocationRepositoryInterface;
use Barialay\AfghanistanProvinceDistrictVillage\Repositories\JsonLocationRepository;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AfghanistanServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/afghanistan-province-district-village.php',
            'afghanistan-province-district-village'
        );

        $this->app->singleton(LocationRepositoryInterface::class, function () {
            return new JsonLocationRepository(
                $this->resolveDataPath('villages_file', 'villages.json'),
                $this->resolveDataPath('provinces_file', 'provinces-and-districts.json')
            );
        });

        $this->app->singleton(Afghanistan::class, function ($app) {
            return new Afghanistan(
                $app->make(LocationRepositoryInterface::class),
                (string) config('afghanistan-province-district-village.default_locale', 'en')
            );
        });

        $this->app->alias(Afghanistan::class, 'afghanistan');
    }

    /**
     * Resolve a data file path from config, published resources, or the package bundle.
     */
    private function resolveDataPath(string $configKey, string $filename): string
    {
        $configured = config("afghanistan-province-district-village.{$configKey}");

        if (! empty($configured) && is_readable((string) $configured)) {
            return (string) $configured;
        }

        $candidates = [];

        if (function_exists('resource_path')) {
            $candidates[] = resource_path("afghanistan-province-district-village/{$filename}");
        }

        $candidates[] = __DIR__."/../resources/data/{$filename}";

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        $attempted = $candidates;

        if (! empty($configured)) {
            array_unshift($attempted, (string) $configured);
        }

        throw new RuntimeException(
            "Afghanistan data file not found or not readable: {$filename}. "
            .'Tried: '.implode(', ', $attempted).'. '
            .'Publish data with: php artisan vendor:publish --tag=afghanistan-province-district-village-data'
        );
    }

    public function boot()
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
