<?php

namespace LemonSqueezy\Laravel\Database\Factories;

use LemonSqueezy\Laravel\Order;
use LemonSqueezy\Laravel\Customer;
use LemonSqueezy\Laravel\Discount;
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
        $customer = Customer::factory()->create();
        $discount = Discount::factory()->create();
        $order = Order::factory()->create();

        return [
            'billable_id' => $customer->id,
            'billable_type' => 'LemonSqueezy\\Laravel\\Customer',
            'lemon_squeezy_id' => $customer->lemon_squeezy_id,
            'discount_id' => $discount->id,
            'order_id' => $order->id,
            'discount_name' => $this->faker->word,
            'discount_code' => $this->faker->unique()->bothify('##??##'),
            'discount_amount' => rand(1, 1000),
            'discount_amount_type' => $this->faker->randomElement(['fixed', 'percent']),
            'amount' => rand(1, 1000),
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