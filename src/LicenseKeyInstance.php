<?php

namespace LemonSqueezy\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use LemonSqueezy\Laravel\Exceptions\MalformedDataError;

class LicenseKeyInstance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemon_squeezy_license_key_instances';

    protected $guarded = [];

    /**
     * @throws MalformedDataError
     */
    public static function fromPayload(array $payload): static {
        $validator = Validator::make($payload, [
            'license_key.id'   => ['required'],
            'instance.id'      => ['required', 'string'],
            'instance.name'    => ['required', 'string'],
        ]);

        if (!$validator->passes()) {
            throw MalformedDataError::forLicenseKey($validator);
        }

        $licenseKey = LicenseKey::notDisabled()->withKey($payload['license_key']['key'])->first();

        return LicenseKeyInstance::create([
            'identifier' => $payload['instance']['id'],
            'license_key_id' => $licenseKey->id,
            'name' => $payload['instance']['name'],
        ]);
    }

    public function licenseKey(): BelongsTo {
        return $this->belongsTo(LicenseKey::class);
    }

    public function active(Builder $query): void {
        $query
            ->join('license_keys', 'license_keys.id', '=', 'license_key_instances.license_key_id')
            ->where('license_keys.status', LicenseKey::STATUS_ACTIVE);
    }
}
