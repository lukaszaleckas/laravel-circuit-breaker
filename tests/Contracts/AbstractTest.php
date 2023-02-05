<?php

namespace LaravelCircuitBreaker\Tests\Contracts;

use LaravelCircuitBreaker\CircuitBreakerServiceProvider;
use LaravelCircuitBreaker\Config\Config;
use LaravelCircuitBreaker\Config\ServiceConfig;
use LaravelCircuitBreaker\Factories\CacheDriverFactory;
use LaravelCircuitBreaker\Tests\Traits\FakerTrait;
use Orchestra\Testbench\TestCase;

abstract class AbstractTest extends TestCase
{
    use FakerTrait;

    /** @var string */
    protected string $cachePrefix;

    /** @var int */
    protected int $defaultTimeWindow;

    /** @var int */
    protected int $defaultFailureThreshold;

    /** @var int */
    protected int $defaultOpenTimeWindow;

    /** @var int */
    protected int $defaultHalfOpenTimeWindow;

    /** @var ServiceConfig */
    protected ServiceConfig $testServiceConfig;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cachePrefix               = $this->getFaker()->word;
        $this->defaultTimeWindow         = $this->getFaker()->numberBetween(1, 1000);
        $this->defaultFailureThreshold   = $this->getFaker()->numberBetween(1, 10);
        $this->defaultOpenTimeWindow     = $this->getFaker()->numberBetween(1, 1000);
        $this->defaultHalfOpenTimeWindow = $this->getFaker()->numberBetween(1, 1000);

        $this->testServiceConfig = new ServiceConfig(
            $this->getFaker()->word,
            $this->defaultTimeWindow,
            $this->defaultFailureThreshold,
            $this->defaultOpenTimeWindow,
            $this->defaultHalfOpenTimeWindow
        );

        parent::setUp();
    }

    /**
     * @param mixed $app
     * @return string[]
     */
    protected function getPackageProviders(mixed $app): array
    {
        return [
            CircuitBreakerServiceProvider::class
        ];
    }

    /**
     * @param mixed $app
     * @return void
     */
    protected function defineEnvironment(mixed $app): void
    {
        $app['config']->set(
            Config::buildFullConfigKey(Config::DRIVER_FACTORY),
            CacheDriverFactory::class
        );

        $app['config']->set(
            Config::buildFullConfigKey(Config::CACHE_PREFIX),
            $this->cachePrefix
        );

        $app['config']->set(
            Config::buildFullConfigKey(Config::TIME_WINDOW),
            $this->defaultTimeWindow
        );
        $app['config']->set(
            Config::buildFullConfigKey(Config::FAILURE_COUNT_THRESHOLD),
            $this->defaultFailureThreshold
        );
        $app['config']->set(
            Config::buildFullConfigKey(Config::OPEN_TIME_WINDOW),
            $this->defaultOpenTimeWindow
        );
        $app['config']->set(
            Config::buildFullConfigKey(Config::HALF_OPEN_TIME_WINDOW),
            $this->defaultHalfOpenTimeWindow
        );
    }
}
