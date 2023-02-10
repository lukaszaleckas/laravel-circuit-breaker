<?php

namespace LaravelCircuitBreaker\Tests;

use Exception;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use LaravelCircuitBreaker\Drivers\CacheDriver;
use LaravelCircuitBreaker\Tests\Contracts\BaseTestCase;

class CacheDriverTest extends BaseTestCase
{
    /** @var CacheDriver */
    private CacheDriver $cacheDriver;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDriver = new CacheDriver(
            $this->testServiceConfig,
            app(CacheRepository::class),
            app(ConfigRepository::class)
        );
    }

    /**
     * @return void
     */
    public function testInitialState(): void
    {
        self::assertTrue($this->cacheDriver->isClosed());
        self::assertFalse($this->cacheDriver->isOpen());
        self::assertFalse($this->cacheDriver->isHalfOpen());
    }

    /**
     * @return void
     */
    public function testCanRegisterSuccess(): void
    {
        $this->cacheDriver->registerSuccess();

        $this->testInitialState();
    }

    /**
     * @return void
     */
    public function testCanRegisterFailure(): void
    {
        $times = $this->getFaker()->numberBetween(1, 10);

        self::assertEquals(0, $this->cacheDriver->getFailureCount());

        for ($time = 1; $time < $times; $time++) {
            $this->cacheDriver->registerFailure();

            self::assertEquals($time, $this->cacheDriver->getFailureCount());
        }
    }

    /**
     * @return void
     */
    public function testCircuitOpens(): void
    {
        $this->reachThreshold();

        self::assertFalse($this->cacheDriver->isClosed());
        self::assertTrue($this->cacheDriver->isOpen());
        self::assertFalse($this->cacheDriver->isHalfOpen());
    }

    /**
     * @return void
     */
    public function testCircuitCloses(): void
    {
        $this->reachThreshold();

        $this->flushOpenState();

        self::assertTrue($this->cacheDriver->isHalfOpen());

        $this->cacheDriver->registerSuccess();

        $this->testInitialState();
    }

    /**
     * @return void
     */
    public function testCircuitReopens(): void
    {
        $this->reachThreshold();

        $this->flushOpenState();

        self::assertTrue($this->cacheDriver->isHalfOpen());

        $this->cacheDriver->registerFailure();

        self::assertFalse($this->cacheDriver->isClosed());
        self::assertTrue($this->cacheDriver->isOpen());
        self::assertFalse($this->cacheDriver->isHalfOpen());
    }

    /**
     * @return void
     */
    private function reachThreshold(): void
    {
        for ($time = 0; $time <= $this->defaultFailureThreshold; $time++) {
            $this->cacheDriver->registerFailure();
        }
    }

    /**
     * @return void
     */
    private function flushOpenState(): void
    {
        Cache::forget(
            $this->cachePrefix . ":{$this->testServiceConfig->getName()}:" . CacheDriver::KEY_IS_OPEN
        );
    }
}
