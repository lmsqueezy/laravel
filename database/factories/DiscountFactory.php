<?php

namespace LemonSqueezy\Laravel\Database\Factories;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use LemonSqueezy\Laravel\Customer;
use LemonSqueezy\Laravel\Discount;

class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amountType = $this->faker->randomElement(['percent', 'fixed']);
        $amount = $amountType === 'percent' ? $this->faker->numberBetween(1, 100) : $this->faker->numberBetween(100, 10000); // Percentage or amount in cents

        return [
            'billable_id' => rand(1, 1000),
            'billable_type' => 'App\\Models\\User',
            'lemon_squeezy_id' => rand(1, 1000),
            'name' => $this->faker->word,
            'code' => $this->faker->regexify('[A-Z0-9]{3,256}'),
            'amount' => $amount,
            'amount_type' => $amountType,
            'is_limited_to_products' => $this->faker->boolean,
            'is_limited_redemptions' => $isLimitedRedemptions = $this->faker->boolean,
            'max_redemptions' => $isLimitedRedemptions ? $this->faker->numberBetween(1, 1000) : null,
            'starts_at' => ($start = $this->faker->optional()->dateTime) ? $start->format(DateTimeInterface::ATOM) : null,
            'expires_at' => ($expire = $this->faker->optional()->dateTime) ? $expire->format(DateTimeInterface::ATOM) : null,
            'duration' => $duration = $this->faker->randomElement(['once', 'repeating', 'forever']),
            'duration_in_months' => $duration === 'repeating' ? $this->faker->numberBetween(1, 12) : null,
            'status' => $this->faker->randomElement(['draft', 'published']),
        ];
    }

    /**
     * Mark the discount as active.
     */
    public function activeDiscount(): self
    {
        return $this->state([
            'starts_at' => now()->subDay(),
            'expires_at' => null,
            'status' => 'active',
        ]);
    }

    /**
     * Mark the discount as expired.
     */
    public function expiredDiscount(): self
    {
        return $this->state([
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->subDay(),
            'status' => 'expired',
        ]);
    }
}
