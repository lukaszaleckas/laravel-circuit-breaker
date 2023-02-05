# Laravel Circuit Breaker

>Circuit breaker is a design pattern used in software development.
It is used to detect failures and encapsulates the logic of preventing
a failure from constantly recurring, during maintenance, temporary external
system failure or unexpected system difficulties.

Source: [Wikipedia](https://en.wikipedia.org/wiki/Circuit_breaker_design_pattern)

## Further explanation

Let's explain what this does using an example, as they
are much better than definitions, in my opinion.

Let's say you have two microservices `A` and `B`:

1. You are calling microservice `B` from `A` and have a 30s timeout configured.
2. `B` fails to respond and you receive a timeout error.
3. You keep getting requests to `A` and keep failing with a timeout from `B`.
4. Request queue of `A` gets filled up.
5. Requests to `A` start to timeout.

In this case it would be much better to track request failures
from `A` to `B` and return an error immediately.

Circuit Breaker pattern does just that - on `A` you are tracking
request failures to `B` and if failure count, in the particular
time window, exceeds threshold, you skip doing those requests
and return an error immediately.

## Installation

1. Run:

```
composer require lukaszaleckas/laravel-circuit-breaker
```

Service provider should be automatically registered, if not add

```php
LaravelCircuitBreaker\CircuitBreakerServiceProvider::class
```

to your application's `app.php`.

2. Publish `circuit-breaker.php` config file:

```
    php artisan vendor:publish --tag=circuit-breaker
```

## Usage

Inject `LaravelCircuitBreaker\CircuitBreakerService` through constructor.

Use `getCircuitBreaker` with service name passed as a parameter 
to get a circuit breaker instance.

Method usage:

* Use `isClosed` in your, for example, HTTP client to determine
if requests can be made.
* Use `registerFailure` when you receive an error.
* Use `registerSuccess` when you receive a success response.
This is used to close the circuit when/if it's half open.
