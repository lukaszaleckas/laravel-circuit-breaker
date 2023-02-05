<?php

namespace LaravelCircuitBreaker\Drivers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use LaravelCircuitBreaker\Config\Config;
use LaravelCircuitBreaker\Config\ServiceConfig;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;

class CacheDriver extends AbstractDriver
{
    public const KEY_IS_OPEN       = 'is_open';
    public const KEY_IS_HALF_OPEN  = 'is_half_open';
    public const KEY_FAILURE_COUNT = 'failure_count';

    public const KEYS = [
        self::KEY_IS_OPEN,
        self::KEY_IS_HALF_OPEN,
        self::KEY_FAILURE_COUNT
    ];

    /** @var string */
    private string $cachePrefix;

    /**
     * @param ServiceConfig    $serviceConfig
     * @param CacheRepository  $cacheRepository
     * @param ConfigRepository $configRepository
     */
    public function __construct(
        ServiceConfig $serviceConfig,
        private CacheRepository $cacheRepository,
        private ConfigRepository $configRepository
    ) {
        parent::__construct($serviceConfig);

        $this->cachePrefix = $this->configRepository->get(
            Config::buildFullConfigKey(
                Config::CACHE_PREFIX
            )
        );
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->cacheRepository->get(
            $this->buildKey(self::KEY_IS_OPEN),
            false
        );
    }

    /**
     * @return bool
     */
    public function isHalfOpen(): bool
    {
        return !$this->isOpen() && $this->cacheRepository->get(
            $this->buildKey(self::KEY_IS_HALF_OPEN),
            false
        );
    }

    /**
     * @return int
     */
    public function getFailureCount(): int
    {
        return $this->cacheRepository->get(
            $this->buildKey(self::KEY_FAILURE_COUNT),
            0
        );
    }

    /**
     * @return void
     */
    protected function openCircuit(): void
    {
        $this->cacheRepository->add(
            $this->buildKey(self::KEY_IS_OPEN),
            true,
            $this->getServiceConfig()->getOpenTimeWindow()
        );

        $this->cacheRepository->put(
            $this->buildKey(self::KEY_IS_HALF_OPEN),
            true,
            $this->getServiceConfig()->getOpenTimeWindow()
                + $this->getServiceConfig()->getHalfOpenTimeWindow()
        );
    }

    /**
     * @return void
     */
    protected function closeCircuit(): void
    {
        $this->cacheRepository->deleteMultiple(
            array_map(
                fn (string $key) => $this->buildKey($key),
                self::KEYS
            )
        );
    }

    /**
     * @return void
     */
    protected function incrementFailureCount(): void
    {
        $key                = $this->buildKey(self::KEY_FAILURE_COUNT);
        $failureCountExists = $this->cacheRepository->has($key);

        if (!$failureCountExists) {
            $this->cacheRepository->add(
                $key,
                1,
                $this->getServiceConfig()->getTimeWindow()
            );

            return;
        }

        $this->cacheRepository->increment($key);
    }

    /**
     * @param string $name
     * @return string
     */
    private function buildKey(string $name): string
    {
        return sprintf(
            '%s:%s:%s',
            $this->cachePrefix,
            $this->getServiceConfig()->getName(),
            $name
        );
    }
}
