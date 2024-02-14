<?php

namespace LemonSqueezy\Laravel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LemonSqueezy\Laravel\Database\Factories\CustomerFactory;

/**
 * @property \LemonSqueezy\Laravel\Billable $billable
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */

    public function getTable()
    {
        return config('lemon-squeezy.tables.customers', 'lemon_squeezy_customers');
    }

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the billable model related to the customer.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine if the customer is on a "generic" trial at the model level.
     */
    public function onGenericTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the customer has an expired "generic" trial at the model level.
     */
    public function hasExpiredGenericTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
