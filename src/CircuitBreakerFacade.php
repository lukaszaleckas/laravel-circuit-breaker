<?php

namespace LaravelCircuitBreaker;

use Illuminate\Support\Facades\Facade;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;

/**
 * @method static AbstractDriver getCircuitBreaker(string $serviceName)
 *
 * @see CircuitBreakerService
 */
class CircuitBreakerFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return CircuitBreakerService::class;
    }
}
