<?php

namespace LaravelCircuitBreaker\Tests;

use LaravelCircuitBreaker\CircuitBreakerService;
use LaravelCircuitBreaker\Config\Config;
use LaravelCircuitBreaker\Config\ServiceConfig;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;
use LaravelCircuitBreaker\Tests\Contracts\AbstractTest;

class CircuitBreakerServiceTest extends AbstractTest
{
    /** @var CircuitBreakerService */
    private CircuitBreakerService $circuitBreakerService;

    /** @var string */
    private string $serviceName;

    /** @var int */
    private int $timeWindow;

    /** @var int */
    private int $failureThreshold;

    /** @var int */
    private int $openTimeWindow;

    /** @var int */
    private int $halfOpenTimeWindow;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->serviceName        = $this->getFaker()->word;
        $this->timeWindow         = $this->getFaker()->numberBetween();
        $this->failureThreshold   = $this->getFaker()->numberBetween();
        $this->openTimeWindow     = $this->getFaker()->numberBetween();
        $this->halfOpenTimeWindow = $this->getFaker()->numberBetween();

        parent::setUp();

        $this->circuitBreakerService = app(CircuitBreakerService::class);
    }

    /**
     * @return void
     */
    public function testCanGetDefinedCircuitBreaker(): void
    {
        $result = $this->circuitBreakerService->getCircuitBreaker($this->serviceName);

        self::assertInstanceOf(AbstractDriver::class, $result);

        $config = $result->getServiceConfig();

        self::assertEquals($this->serviceName, $config->getName());
        self::assertEquals($this->timeWindow, $config->getTimeWindow());
        self::assertEquals($this->failureThreshold, $config->getFailureCountThreshold());
        self::assertEquals($this->openTimeWindow, $config->getOpenTimeWindow());
        self::assertEquals($this->halfOpenTimeWindow, $config->getHalfOpenTimeWindow());
    }

    /**
     * @return void
     */
    public function testCanGetUndefinedCircuitBreaker(): void
    {
        $result          = $this->circuitBreakerService->getCircuitBreaker(
            $serviceName = $this->getFaker()->word
        );

        self::assertInstanceOf(AbstractDriver::class, $result);

        $config = $result->getServiceConfig();

        self::assertEquals($serviceName, $config->getName());
        self::assertEquals($this->defaultTimeWindow, $config->getTimeWindow());
        self::assertEquals($this->defaultFailureThreshold, $config->getFailureCountThreshold());
        self::assertEquals($this->defaultOpenTimeWindow, $config->getOpenTimeWindow());
        self::assertEquals($this->defaultHalfOpenTimeWindow, $config->getHalfOpenTimeWindow());
    }

    /**
     * @param mixed $app
     * @return void
     */
    protected function defineEnvironment(mixed $app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set(
            Config::buildFullConfigKey(Config::SERVICES),
            [
                [
                    ServiceConfig::NAME                    => $this->serviceName,
                    ServiceConfig::TIME_WINDOW             => $this->timeWindow,
                    ServiceConfig::FAILURE_COUNT_THRESHOLD => $this->failureThreshold,
                    ServiceConfig::OPEN_TIME_WINDOW        => $this->openTimeWindow,
                    ServiceConfig::HALF_OPEN_TIME_WINDOW   => $this->halfOpenTimeWindow
                ]
            ]
        );
    }
}
