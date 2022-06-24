<?php

namespace Throwexceptions\LaravelGridjs\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelGridjs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-gridjs';
    }
}
