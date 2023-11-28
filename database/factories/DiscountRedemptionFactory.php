<?php

namespace LemonSqueezy\Laravel\Database\Factories;

use LemonSqueezy\Laravel\Customer;
use LemonSqueezy\Laravel\DiscountRedemption;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountRedemptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountRedemption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'billable_id' => rand(1, 1000),
            'billable_type' => 'App\\Models\\User',
            'lemon_squeezy_id' => rand(1, 1000),
            'discount_id' => rand(1, 1000),
            'order_id' => rand(1, 1000),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): self
    {
        return $this->afterCreating(function ($subscription) {
            Customer::factory()->create([
                'billable_id' => $subscription->billable_id,
                'billable_type' => $subscription->billable_type,
            ]);
        });
    }
}