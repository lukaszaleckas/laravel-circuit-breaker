<?php

namespace LaravelCircuitBreaker\Tests\Traits;

use Faker\Factory;
use Faker\Generator;

trait FakerTrait
{
    /** @var Generator */
    private Generator $faker;

    /**
     * @return Generator
     */
    public function getFaker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = $faker = Factory::create();
        }

        return $this->faker;
    }
}
