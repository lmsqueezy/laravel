<?php

namespace LemonSqueezy\Laravel\Database\Factories;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use LemonSqueezy\Laravel\Customer;
use LemonSqueezy\Laravel\Order;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

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
            'customer_id' => rand(1, 1000),
            'product_id' => rand(1, 1000),
            'variant_id' => rand(1, 1000),
            'order_number' => rand(1, 1000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'subtotal' => $subtotal = rand(400, 1000),
            'discount_total' => $discount = rand(1, 400),
            'tax' => $tax = rand(1, 50),
            'total' => $subtotal - $discount + $tax,
            'tax_name' => $this->faker->randomElement(['VAT', 'Sales Tax']),
            'receipt_url' => null,
            'ordered_at' => $orderedAt = Carbon::make($this->faker->dateTimeBetween('-1 year', 'now')),
            'refunded' => $refunded = $this->faker->boolean(75),
            'refunded_at' => $refunded ? $orderedAt->addWeek() : null,
            'status' => $refunded ? Order::STATUS_REFUNDED : Order::STATUS_PAID,
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

    /**
     * Mark the order as pending.
     */
    public function pending(): self
    {
        return $this->state([
            'status' => Order::STATUS_PENDING,
            'refunded' => false,
            'refunded_at' => null,
        ]);
    }

    /**
     * Mark the order as failed.
     */
    public function failed(): self
    {
        return $this->state([
            'status' => Order::STATUS_FAILED,
            'refunded' => false,
            'refunded_at' => null,
        ]);
    }

    /**
     * Mark the order as paid.
     */
    public function paid(): self
    {
        return $this->state([
            'status' => Order::STATUS_PAID,
            'refunded' => false,
            'refunded_at' => null,
        ]);
    }

    /**
     * Mark the order as being refunded.
     */
    public function refunded(?DateTimeInterface $refundedAt = null): self
    {
        return $this->state([
            'status' => Order::STATUS_REFUNDED,
            'refunded' => true,
            'refunded_at' => $refundedAt,
        ]);
    }
}
