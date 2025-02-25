<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Concerns;

use LemonSqueezy\Laravel\Billable;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Order;
use LemonSqueezy\Laravel\Subscription;

trait CanResolveBillable
{
    /**
     * Resolve the Billable instance from the webhook payload.
     *
     * @param array $payload
     * @return Billable
     * @throws InvalidCustomPayload
     */
    protected function resolveBillable(array $payload): Billable
    {
        $custom = $payload['meta']['custom_data'] ?? null;

        if (!isset($custom['billable_id'], $custom['billable_type']) || !is_array($custom)) {
            throw new InvalidCustomPayload();
        }

        return $this->findOrCreateCustomer(
            $custom['billable_id'],
            (string) $custom['billable_type'],
            (string) $payload['data']['attributes']['customer_id'],
        );
    }

    /**
     * Find or create a Customer by its Lemon Squeezy ID.
     *
     * @param int|string $billableId
     * @param string $billableType
     * @param string $customerId
     * @return Billable
     */
    protected function findOrCreateCustomer(int|string $billableId, string $billableType, string $customerId): Billable
    {
        return LemonSqueezy::$customerModel::firstOrCreate([
            'billable_id' => $billableId,
            'billable_type' => $billableType,
        ], [
            'lemon_squeezy_id' => $customerId,
        ])->billable;
    }

    /**
     * Find a Subscription by its Lemon Squeezy ID.
     *
     * @param string $subscriptionId
     * @return Subscription|null
     */
    protected function findSubscription(string $subscriptionId): ?Subscription
    {
        return LemonSqueezy::$subscriptionModel::firstWhere('lemon_squeezy_id', $subscriptionId);
    }

    /**
     * Find an Order by its Lemon Squeezy ID.
     *
     * @param string $orderId
     * @return Order|null
     */
    protected function findOrder(string $orderId): ?Order
    {
        return LemonSqueezy::$orderModel::firstWhere('lemon_squeezy_id', $orderId);
    }
}
