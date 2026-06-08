<?php

namespace CleaniqueCoders\ConfigSso\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool enabled()
 * @method static \Illuminate\Support\Collection providers()
 * @method static array<string, string> drivers()
 * @method static array<int, string> driverFields(string $driver)
 *
 * @see \CleaniqueCoders\ConfigSso\ConfigSso
 */
class ConfigSso extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\ConfigSso\ConfigSso::class;
    }
}
