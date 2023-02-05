<?php

namespace LaravelCircuitBreaker\Config;

class ServiceConfig
{
    public const NAME                    = 'name';
    public const TIME_WINDOW             = 'time_window';
    public const FAILURE_COUNT_THRESHOLD = 'failure_count_threshold';
    public const OPEN_TIME_WINDOW        = 'open_time_window';
    public const HALF_OPEN_TIME_WINDOW   = 'half_open_time_window';

    /**
     * @param string $name
     * @param int    $timeWindow
     * @param int    $failureCountThreshold
     * @param int    $openTimeWindow
     * @param int    $halfOpenTimeWindow
     */
    public function __construct(
        private string $name,
        private int $timeWindow,
        private int $failureCountThreshold,
        private int $openTimeWindow,
        private int $halfOpenTimeWindow
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getTimeWindow(): int
    {
        return $this->timeWindow;
    }

    /**
     * @return int
     */
    public function getFailureCountThreshold(): int
    {
        return $this->failureCountThreshold;
    }

    /**
     * @return int
     */
    public function getOpenTimeWindow(): int
    {
        return $this->openTimeWindow;
    }

    /**
     * @return int
     */
    public function getHalfOpenTimeWindow(): int
    {
        return $this->halfOpenTimeWindow;
    }

    /**
     * @param array $data
     * @return self
     */
    public static function buildFromArray(array $data): self
    {
        return new self(
            $data[self::NAME],
            $data[self::TIME_WINDOW],
            $data[self::FAILURE_COUNT_THRESHOLD],
            $data[self::OPEN_TIME_WINDOW],
            $data[self::HALF_OPEN_TIME_WINDOW]
        );
    }
}
