<?php

namespace LaravelLemonSqueezy\Database\Factories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelLemonSqueezy\Customer;
use LaravelLemonSqueezy\Subscription;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

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
            'type' => 'default',
            'lemon_squeezy_id' => rand(1, 1000),
            'status' => Subscription::STATUS_ACTIVE,
            'product_id' => rand(1, 1000),
            'variant_id' => rand(1, 1000),
            'card_brand' => $this->faker->randomElement(['visa', 'mastercard', 'american_express', 'discover', 'jcb', 'diners_club']),
            'card_last_four' => rand(1000, 9999),
            'pause_mode' => null,
            'pause_resumes_at' => null,
            'trial_ends_at' => null,
            'renews_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($subscription) {
            Customer::factory()->create([
                'billable_id' => $subscription->billable_id,
                'billable_type' => $subscription->billable_type,
                'lemon_squeezy_id' => $subscription->lemon_squeezy_id,
            ]);
        });
    }

    /**
     * Mark the subscription as being within a trial period.
     */
    public function trialing(DateTimeInterface $trialEndsAt = null): static
    {
        return $this->state([
            'status' => Subscription::STATUS_ON_TRIAL,
            'trial_ends_at' => $trialEndsAt,
        ]);
    }

    /**
     * Mark the subscription as active.
     */
    public function active(): static
    {
        return $this->state([
            'status' => Subscription::STATUS_ACTIVE,
        ]);
    }

    /**
     * Mark the subscription as paused.
     */
    public function paused(DateTimeInterface $resumesAt = null): static
    {
        return $this->state([
            'status' => Subscription::STATUS_PAUSED,
            'pause_mode' => $this->faker->randomElement(['void', 'free']),
            'pause_resumes_at' => $resumesAt,
        ]);
    }

    /**
     * Mark the subscription as past due.
     */
    public function pastDue(): static
    {
        return $this->state([
            'status' => Subscription::STATUS_PAST_DUE,
        ]);
    }

    /**
     * Mark the subscription as unpaid.
     */
    public function unpaid(): static
    {
        return $this->state([
            'status' => Subscription::STATUS_UNPAID,
        ]);
    }

    /**
     * Mark the subscription as cancelled.
     */
    public function cancelled(): static
    {
        return $this->state([
            'status' => Subscription::STATUS_CANCELLED,
            'ends_at' => now(),
        ]);
    }

    /**
     * Mark the subscription as expired
     */
    public function expired(): static
    {
        return $this->state([
            'status' => Subscription::STATUS_EXPIRED,
        ]);
    }
}
