<?php

namespace LaravelCircuitBreaker\Tests\Guzzle;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use LaravelCircuitBreaker\CircuitBreakerService;
use LaravelCircuitBreaker\Drivers\Contracts\AbstractDriver;
use LaravelCircuitBreaker\Exceptions\CircuitBreakerException;
use LaravelCircuitBreaker\Guzzle\CircuitBreakerMiddleware;
use LaravelCircuitBreaker\Tests\Contracts\BaseTestCase;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CircuitBreakerMiddlewareTest extends BaseTestCase
{
    /** @var CircuitBreakerMiddleware */
    private CircuitBreakerMiddleware $middleware;

    /** @var MockInterface */
    private MockInterface $driverMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->driverMock = Mockery::mock(AbstractDriver::class);

        $circuitBreakerServiceMock = Mockery::mock(CircuitBreakerService::class)
            ->shouldReceive('getCircuitBreaker')
            ->andReturn($this->driverMock)
            ->getMock();

        $this->middleware = new CircuitBreakerMiddleware(
            $circuitBreakerServiceMock
        );
    }

    /**
     * @return void
     */
    public function testThrowsExceptionIfCircuitOpen(): void
    {
        $this->expectException(CircuitBreakerException::class);

        $this->driverMock->shouldReceive('isOpen')->once()->andReturnTrue();

        ($this->middleware)('')(
            function () {
            }
        )(
            Mockery::mock(RequestInterface::class),
            []
        );
    }

    /**
     * @dataProvider skipableResponseTestDataProvider
     *
     * @param int            $statusCode
     * @param bool           $shouldRegisterSuccess
     * @param bool           $shouldRegisterFailure
     * @param Exception|null $exception
     * @return void
     */
    public function testHandlesResponse(
        int $statusCode,
        bool $shouldRegisterSuccess = false,
        bool $shouldRegisterFailure = false,
        Exception $exception = null
    ): void {
        $this->driverMock->shouldReceive('isOpen')->once()->andReturnFalse();

        if ($exception) {
            $this->expectExceptionObject($exception);
        }

        if ($shouldRegisterSuccess) {
            $this->driverMock->shouldReceive('registerSuccess')->once();
        }

        if ($shouldRegisterFailure) {
            $this->driverMock->shouldReceive('registerFailure')->once();
        }

        $responseMock = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode)
            ->getMock();
        $promiseMock  = Mockery::mock(PromiseInterface::class)
            ->shouldReceive('then')
            ->withArgs(
                function (
                    callable $responseCallback,
                    callable $exceptionCallback
                ) use (
                    $responseMock,
                    $exception
                ) {
                    if ($exception) {
                        $exceptionCallback($exception);
                    } else {
                        $responseCallback($responseMock);
                    }

                    return true;
                }
            )
            ->andReturnSelf()
            ->getMock();

        ($this->middleware)('')(
            fn () => $promiseMock
        )(
            Mockery::mock(RequestInterface::class),
            []
        );
    }

    /**
     * @return array
     */
    public function skipableResponseTestDataProvider(): array
    {
        return [
            'Redirect response'     => [
                $this->getFaker()->numberBetween(300, 399)
            ],
            'Client error response' => [
                $this->getFaker()->numberBetween(400, 499)
            ],
            'Success response'      => [
                $this->getFaker()->numberBetween(200, 299),
                true
            ],
            'Error response'        => [
                $this->getFaker()->numberBetween(500, 599),
                false,
                true
            ],
            'Exception'             => [
                $this->getFaker()->numberBetween(500, 599),
                false,
                true,
                new Exception()
            ],
        ];
    }
}
