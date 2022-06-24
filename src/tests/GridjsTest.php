<?php

namespace Throwexceptions\LaravelGridjs\Tests;

use Orchestra\Testbench\TestCase;
use Throwexceptions\LaravelGridjs\LaravelGridjsServiceProvider;
use Illuminate\Foundation\Application as ApplicationAlias;

class GridjsTest extends TestCase
{
    /**
     * @param  ApplicationAlias  $app
     * @return string[]
     */
    protected function getPackageProviders(ApplicationAlias $app)
    {
        return [
          LaravelGridjsServiceProvider::class
        ];
    }
}
