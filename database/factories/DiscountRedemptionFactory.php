<?php

namespace LemonSqueezy\Laravel\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LemonSqueezy\Laravel\DiscountRedemption;
use LemonSqueezy\Laravel\Discount;
use LemonSqueezy\Laravel\Order;

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
            'discount_id' => Discount::factory(),
            'order_id' => Order::factory(),
        ];
    }
}