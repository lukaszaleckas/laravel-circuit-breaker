<?php

namespace LaravelCircuitBreaker\Factories;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use LaravelCircuitBreaker\Drivers\CacheDriver;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;
use LaravelCircuitBreaker\Factories\Contracts\AbstractDriverFactory;

class CacheDriverFactory extends AbstractDriverFactory
{
    /**
     * @param ConfigRepository $configRepository
     * @param CacheRepository  $cacheRepository
     */
    public function __construct(
        protected ConfigRepository $configRepository,
        private CacheRepository $cacheRepository
    ) {
        parent::__construct($configRepository);
    }

    /**
     * @param string $serviceName
     * @return AbstractDriver
     */
    public function buildDriver(string $serviceName): AbstractDriver
    {
        return new CacheDriver(
            $this->buildServiceConfig($serviceName),
            $this->cacheRepository,
            $this->configRepository
        );
    }
}
