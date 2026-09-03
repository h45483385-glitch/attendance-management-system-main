<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'pin_code' => bcrypt('1234'),
            'position' => $this->faker->jobTitle(),
            'department' => 'IT',
            'role' => 'employee',
            'face_enrolled' => false,
            'fingerprint_enrolled' => false,
            'employment_type' => 'full-time',
            'status' => 'active',
        ];
    }
}
