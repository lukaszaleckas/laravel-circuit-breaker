<?php

namespace LaravelCircuitBreaker\Drivers\Contracts;

use LaravelCircuitBreaker\Config\ServiceConfig;

abstract class AbstractDriver
{
    /**
     * @param ServiceConfig $serviceConfig
     */
    public function __construct(private ServiceConfig $serviceConfig)
    {
    }

    /**
     * @return void
     */
    public function registerFailure(): void
    {
        $this->incrementFailureCount();

        if ($this->shouldOpenCircuit()) {
            $this->openCircuit();
        }
    }

    /**
     * @return void
     */
    public function registerSuccess(): void
    {
        if ($this->isHalfOpen()) {
            $this->closeCircuit();
        }
    }

    /**
     * @return ServiceConfig
     */
    public function getServiceConfig(): ServiceConfig
    {
        return $this->serviceConfig;
    }

    /**
     * @return bool
     */
    private function shouldOpenCircuit(): bool
    {
        $isOverThreshold = $this->getFailureCount() > $this->serviceConfig->getFailureCountThreshold();

        return $isOverThreshold || $this->isHalfOpen();
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return !$this->isOpen();
    }

    /**
     * @return bool
     */
    abstract public function isOpen(): bool;

    /**
     * @return bool
     */
    abstract public function isHalfOpen(): bool;

    /**
     * @return int
     */
    abstract public function getFailureCount(): int;

    /**
     * @return void
     */
    abstract protected function openCircuit(): void;

    /**
     * @return void
     */
    abstract protected function closeCircuit(): void;

    /**
     * @return void
     */
    abstract protected function incrementFailureCount(): void;
}
