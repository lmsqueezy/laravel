<?php

namespace LemonSqueezy\Laravel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Validator;
use LemonSqueezy\Laravel\Database\Factories\LicenseKeyFactory;
use LemonSqueezy\Laravel\Exceptions\MalformedDataError;

/**
 * The LicenseKey Key object
 * https://docs.lemonsqueezy.com/api/license-keys/the-license-key-object
 */
class LicenseKey extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DISABLED = 'disabled';
    private const KEY_ID = 'id';
    private const KEY_DISABLED = 'disabled';
    private const KEY_KEY = 'key';
    private const KEY_KEY_SHORT = 'key_short';
    private const KEY_ACTIVATION_LIMIT = 'activation_limit';
    private const KEY_INSTANCES_COUNT = 'instances_count';
    private const KEY_PRODUCT_ID = 'product_id';
    private const KEY_ORDER_ID = 'order_id';
    private const KEY_STATUS = 'status';
    private const KEY_EXPIRES_AT = 'expires_at';
    private const KEY_CREATED_AT = 'created_at';
    private const KEY_UPDATED_AT = 'updated_at';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemon_squeezy_license_keys';

    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'activation_limit' => 'integer',
        'instances_count' => 'integer',
        'expired_at' => 'datetime',
    ];

    /**
     * @throws MalformedDataError
     */
    public static function fromPayload(array $payload): LicenseKey {
        $validator = Validator::make(array_merge([self::KEY_ID => $payload[self::KEY_ID]], $payload['attributes']), [
            self::KEY_ID                => ['required', 'string'],
            self::KEY_KEY               => ['required', 'string'],
            self::KEY_KEY_SHORT         => ['required', 'string'],
            self::KEY_ACTIVATION_LIMIT  => ['nullable', 'numeric'],
            self::KEY_PRODUCT_ID        => ['required'],
            self::KEY_ORDER_ID          => ['required'],
            self::KEY_STATUS            => ['required'],
            self::KEY_CREATED_AT        => ['required', 'date'],
        ]);

        if (!$validator->passes()) {
            throw MalformedDataError::forLicenseKey($validator);
        }

        $attributes = $payload['attributes'];

        $licenseKey = LicenseKey::create([
            'lemon_squeezy_id' => $payload[self::KEY_ID],
            'status' => $attributes[self::KEY_STATUS],
            'disabled' => $attributes[self::KEY_DISABLED] ?? false,
            'license_key' => $attributes[self::KEY_KEY],
            'product_id' => $attributes[self::KEY_PRODUCT_ID],
            'order_id' => $attributes[self::KEY_ORDER_ID],
            'activation_limit' => $attributes[self::KEY_ACTIVATION_LIMIT],
            'instances_count' => $attributes[self::KEY_INSTANCES_COUNT] ?? 0,
            'expires_at' => isset($attributes[self::KEY_EXPIRES_AT])
                ? Carbon::make($attributes[self::KEY_EXPIRES_AT])
                : null,
            'created_at' => $attributes[self::KEY_CREATED_AT],
            'updated_at' => isset($attributes[self::KEY_UPDATED_AT])
                ? Carbon::make($attributes[self::KEY_UPDATED_AT]) : null,
        ]);

        return $licenseKey;
    }

    /**
     * The order this license key was generated for
     */
    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    /**
     * The billable that purchased the license
     */
    public function billable(): Model {
        return $this->order()->first()->billable;
    }

    /**
     * Check if the license key is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Filter query by active.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Filter query by enabled.
     */
    public function scopeNotDisabled(Builder $query): void
    {
        $query->where('disabled', false);
    }

    /**
     * Filter query by license key.
     */
    public function scopeWithKey(Builder $query, string $key): void
    {
        $query->where('license_key', $key);
    }

    /**
     * Check if the license key is inactive.
     */
    public function inactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Filter query by inactive.
     */
    public function scopeInactive(Builder $query): void
    {
        $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Check if the license key is disabled.
     */
    public function disabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * Filter query by disabled.
     */
    public function scopeDisabled(Builder $query): void
    {
        $query->where('status', self::STATUS_DISABLED);
    }

    /**
     * Check if the license key is expired.
     */
    public function expired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Filter query by expired.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Determine if the license is for a specific product.
     */
    public function hasProduct(string $productId): bool
    {
        return $this->product_id === $productId;
    }

    /**
     * Sync the license key with the given payload data.
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'status' => $attributes[self::KEY_STATUS],
            'disabled' => $attributes[self::KEY_DISABLED],
            'product_id' => $attributes[self::KEY_PRODUCT_ID],
            'activation_limit' => $attributes[self::KEY_ACTIVATION_LIMIT],
            'instances_count' => $attributes[self::KEY_INSTANCES_COUNT],
            'expires_at' => isset($attributes[self::KEY_EXPIRES_AT])
                ? Carbon::make($attributes[self::KEY_EXPIRES_AT])
                : null,
            'updated_at' => Carbon::make($attributes[self::KEY_EXPIRES_AT]),
        ]);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): LicenseKeyFactory
    {
        return LicenseKeyFactory::new();
    }
}
