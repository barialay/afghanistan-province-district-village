<?php

namespace Barialay\AfghanistanProvinceDistrictVillage\Tests;

use Barialay\AfghanistanProvinceDistrictVillage\AfghanistanServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AfghanistanServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Afghanistan' => \Barialay\AfghanistanProvinceDistrictVillage\Facades\Afghanistan::class,
        ];
    }
}
