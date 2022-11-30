<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event_name' => $this->faker->name(),
            'fight_date_time' => 'laravel, api, backend',
            'location' => $this->faker->company(),
            'status' => $this->faker->companyEmail(),
            'video_code' => $this->faker->url(),
            'palasada' => $this->faker->randomFloat('2',0,2),
        ];
    }
}
