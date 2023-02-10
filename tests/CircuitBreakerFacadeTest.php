<?php

namespace LaravelCircuitBreaker\Tests;

use LaravelCircuitBreaker\CircuitBreakerFacade;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;
use LaravelCircuitBreaker\Tests\Contracts\BaseTestCase;

class CircuitBreakerFacadeTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testCanGetCircuitBreaker(): void
    {
        self::assertInstanceOf(
            AbstractDriver::class,
            CircuitBreakerFacade::getCircuitBreaker($this->getFaker()->word)
        );
    }
}
