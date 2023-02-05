<?php

namespace LaravelCircuitBreaker\Factories\Contracts;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use LaravelCircuitBreaker\Config\Config;
use LaravelCircuitBreaker\Config\ServiceConfig;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;

abstract class AbstractDriverFactory
{
    /**
     * @param Repository $configRepository
     */
    public function __construct(protected Repository $configRepository)
    {
    }

    /**
     * @param string $serviceName
     * @return ServiceConfig
     */
    protected function buildServiceConfig(string $serviceName): ServiceConfig
    {
        $config = $this->getServiceConfig($serviceName);

        if ($config !== null) {
            return ServiceConfig::buildFromArray($config);
        }

        return new ServiceConfig(
            $serviceName,
            $this->configRepository->get(
                Config::buildFullConfigKey(Config::TIME_WINDOW),
            ),
            $this->configRepository->get(
                Config::buildFullConfigKey(Config::FAILURE_COUNT_THRESHOLD),
            ),
            $this->configRepository->get(
                Config::buildFullConfigKey(Config::OPEN_TIME_WINDOW),
            ),
            $this->configRepository->get(
                Config::buildFullConfigKey(Config::HALF_OPEN_TIME_WINDOW),
            ),
        );
    }

    /**
     * @param string $serviceName
     * @return array|null
     */
    private function getServiceConfig(string $serviceName): ?array
    {
        return Arr::first(
            $this->configRepository->get(
                Config::buildFullConfigKey(
                    Config::SERVICES
                )
            ),
            fn (array $serviceConfig) => $serviceConfig['name'] === $serviceName
        );
    }

    /**
     * @param string $serviceName
     * @return AbstractDriver
     */
    abstract public function buildDriver(string $serviceName): AbstractDriver;
}
