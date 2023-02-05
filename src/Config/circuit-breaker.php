<?php

use LaravelCircuitBreaker\Config\Config;
use LaravelCircuitBreaker\Config\ServiceConfig;
use LaravelCircuitBreaker\Factories\CacheDriverFactory;

return [
    Config::DRIVER_FACTORY          => env('CIRCUIT_BREAKER_DRIVER_FACTORY', CacheDriverFactory::class),
    Config::CACHE_PREFIX            => env('CIRCUIT_BREAKER_CACHE_PREFIX', 'circuit_breaker'),

    /*
     * Used as defaults when creating a circuit breaker which is
     * not configured in SERVICES.
     */
    Config::TIME_WINDOW             => env('CIRCUIT_BREAKER_TIME_WINDOW', 60),
    Config::FAILURE_COUNT_THRESHOLD => env('CIRCUIT_BREAKER_FAILURE_COUNT_THRESHOLD', 40),
    Config::OPEN_TIME_WINDOW        => env('CIRCUIT_BREAKER_OPEN_TIME_WINDOW', 60),
    Config::HALF_OPEN_TIME_WINDOW   => env('CIRCUIT_BREAKER_HALF_OPEN_TIME_WINDOW', 30),

    Config::SERVICES                => [
        /*
         * Service config, named "service_a", added as an example.
         */
        [
            /*
             * Service name, can be anything, but must be unique.
             */
            ServiceConfig::NAME                    => 'service_a',
            /*
             * Time window in seconds.
             */
            ServiceConfig::TIME_WINDOW             => 60,
            /*
             * Failure, for example failed HTTP requests, threshold.
             * If failure count exceeds this threshold in configured TIME_WINDOW,
             * circuit breaker opens.
             */
            ServiceConfig::FAILURE_COUNT_THRESHOLD => 40,
            /*
             * Time in seconds to open the circuit breaker if FAILURE_COUNT_THRESHOLD
             * is exceeded.
             */
            ServiceConfig::OPEN_TIME_WINDOW        => 60,
            /*
             * Time in seconds to half open the circuit breaker.
             * If request succeeds in this time window circuit breaker
             * is closed, otherwise it's opened up again.
             */
            ServiceConfig::HALF_OPEN_TIME_WINDOW   => 30
        ]
    ]
];
