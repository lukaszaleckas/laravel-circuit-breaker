<?php

namespace LaravelCircuitBreaker;

use Illuminate\Config\Repository;
use LaravelCircuitBreaker\Config\Config;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;
use LaravelCircuitBreaker\Factories\Contracts\AbstractDriverFactory;

class CircuitBreakerService
{
    /** @var array */
    private array $circuitBreakers;
    
    /** @var string */
    private string $driverFactoryClass;

    /**
     * @param Repository $configRepository
     */
    public function __construct(Repository $configRepository)
    {
        $this->circuitBreakers = [];

        $this->driverFactoryClass = $configRepository->get(
            Config::buildFullConfigKey(
                Config::DRIVER_FACTORY
            )
        );
    }

    /**
     * @param string $serviceName
     * @return AbstractDriver
     */
    public function getCircuitBreaker(string $serviceName): AbstractDriver
    {
        if (!$this->hasCircuitBreaker($serviceName)) {
            $this->circuitBreakers[$serviceName] = $this->buildCircuitBreaker($serviceName);
        }

        return $this->circuitBreakers[$serviceName];
    }

    /**
     * @param string $serviceName
     * @return AbstractDriver
     */
    private function buildCircuitBreaker(string $serviceName): AbstractDriver
    {
        /** @var AbstractDriverFactory $factory */
        $factory = app($this->driverFactoryClass);

        return $factory->buildDriver($serviceName);
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    private function hasCircuitBreaker(string $serviceName): bool
    {
        return isset($this->circuitBreakers[$serviceName]);
    }
}
