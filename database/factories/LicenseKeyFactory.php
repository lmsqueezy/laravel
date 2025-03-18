<?php

namespace LemonSqueezy\Laravel\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LemonSqueezy\Laravel\LicenseKey;

class LicenseKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LicenseKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lemon_squeezy_id' => rand(1, 1000),
            'status' => LicenseKey::STATUS_ACTIVE,
            'disabled' => false,
            'license_key' => $this->faker->uuid(),
            'product_id' => rand(1, 1000),
            'order_id' => rand(1, 1000),
            'activation_limit' => 0,
            'instances_count' =>  0,
            'expires_at' => null,
            'updated_at' => null
        ];
    }

    /**
     * Mark the license key as active.
     */
    public function active(): self
    {
        return $this->state([
            'status' => LicenseKey::STATUS_ACTIVE,
        ]);
    }

    /**
     * Disable the license key.
     */
    public function disable(): self
    {
        return $this->state([
            'disabled' => true,
            'status' => LicenseKey::STATUS_DISABLED,
        ]);
    }
}
