<?php

namespace LaravelCircuitBreaker\Config;

class Config
{
    public const CONFIG_NAME = 'circuit-breaker';

    public const DRIVER_FACTORY = 'driver_factory';
    public const SERVICES       = 'services';
    public const CACHE_PREFIX   = 'cache_prefix';

    public const TIME_WINDOW             = 'time_window';
    public const FAILURE_COUNT_THRESHOLD = 'failure_count_threshold';
    public const OPEN_TIME_WINDOW        = 'open_time_window';
    public const HALF_OPEN_TIME_WINDOW   = 'half_open_time_window';

    /**
     * @param string $name
     * @return string
     */
    public static function buildFullConfigKey(string $name): string
    {
        return self::CONFIG_NAME . ".$name";
    }
}
