<?php

namespace Modules\DayTour\Database\Factories;

use Modules\DayTour\App\Models\DayTour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DayTourFactory extends Factory
{
    protected $model = DayTour::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::ulid(),
            'agency_id' => fake()->uuid(),
            'city_id' => fake()->numberBetween(1, 100),
            'destination_id' => fake()->numberBetween(1, 50),
            'title_translations' => [
                [
                    'locale' => 'en',
                    'value' => fake()->sentence(4),
                ],
                [
                    'locale' => 'ar',
                    'value' => fake()->sentence(4),
                ],
            ],
            'description_translations' => [
                [
                    'locale' => 'en',
                    'value' => fake()->paragraph(),
                ],
                [
                    'locale' => 'ar',
                    'value' => fake()->paragraph(),
                ],
            ],
            'is_active' => true,
            'is_shared' => fake()->boolean(30),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function shared(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_shared' => true,
        ]);
    }
}
