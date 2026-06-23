<?php

declare(strict_types=1);

namespace SDOSA\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SDOSA\Laravel\HijriServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [HijriServiceProvider::class];
    }
}
