<?php

namespace LaravelCircuitBreaker\Guzzle;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use LaravelCircuitBreaker\CircuitBreakerService;
use LaravelCircuitBreaker\Exceptions\CircuitBreakerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class CircuitBreakerMiddleware
{
    /**
     * @param CircuitBreakerService $circuitBreakerService
     */
    public function __construct(private CircuitBreakerService $circuitBreakerService)
    {
    }

    /**
     * @param string $serviceName
     * @return Closure
     */
    public function __invoke(string $serviceName): Closure
    {
        return fn (callable $handler) =>
            function (RequestInterface $request, array $options) use ($handler, $serviceName) {
                return $this->buildPromise(
                    $request,
                    $options,
                    $handler,
                    $serviceName
                );
            };
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     * @param callable         $handler
     * @param string           $serviceName
     * @return PromiseInterface
     * @throws CircuitBreakerException
     */
    private function buildPromise(
        RequestInterface $request,
        array $options,
        callable $handler,
        string $serviceName
    ): PromiseInterface {
        $this->ensureCircuitIsClosed($serviceName);

        /** @var PromiseInterface $promise */
        $promise = $handler($request, $options);

        return $promise->then(
            function (ResponseInterface $response) use ($serviceName) {
                $this->handleResponse($response, $serviceName);

                return $response;
            },
            function (Throwable $exception) use ($serviceName) {
                $this->circuitBreakerService
                    ->getCircuitBreaker($serviceName)
                    ->registerFailure();

                throw $exception;
            },
        );
    }

    /**
     * @param ResponseInterface $response
     * @param string            $serviceName
     * @return void
     */
    private function handleResponse(ResponseInterface $response, string $serviceName): void
    {
        $statusCode     = $response->getStatusCode();
        $circuitBreaker = $this->circuitBreakerService->getCircuitBreaker($serviceName);

        if ($this->isRedirectStatusCode($statusCode) || $this->isClientErrorStatusCode($statusCode)) {
            return;
        }

        $this->isSuccessStatusCode($statusCode)
            ?  $circuitBreaker->registerSuccess()
            : $circuitBreaker->registerFailure();
    }

    /**
     * @param string $serviceName
     * @return void
     * @throws CircuitBreakerException
     */
    private function ensureCircuitIsClosed(string $serviceName): void
    {
        if ($this->circuitBreakerService->getCircuitBreaker($serviceName)->isOpen()) {
            throw new CircuitBreakerException(
                "'$serviceName' service's circuit breaker is open"
            );
        }
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    private function isSuccessStatusCode(int $statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    private function isClientErrorStatusCode(int $statusCode): bool
    {
        return $statusCode >= 400 && $statusCode < 500;
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    private function isRedirectStatusCode(int $statusCode): bool
    {
        return $statusCode >= 300 && $statusCode < 400;
    }
}
