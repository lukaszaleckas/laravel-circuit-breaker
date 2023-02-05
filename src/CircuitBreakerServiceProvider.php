<?php

namespace LaravelCircuitBreaker;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class CircuitBreakerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishConfig();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(CircuitBreakerService::class);
    }

    /**
     * @return string[]
     */
    public function provides(): array
    {
        return [
            CircuitBreakerService::class
        ];
    }

    /**
     * @return void
     */
    private function publishConfig(): void
    {
        $this->publishes(
            [
                __DIR__ . '/Config/circuit-breaker.php' => config_path('circuit-breaker.php'),
            ],
            'circuit-breaker'
        );
    }
}
