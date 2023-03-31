<?php

namespace LaravelLemonSqueezy;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use LaravelLemonSqueezy\Exceptions\ReservedCustomKeys;

class Checkout implements Responsable
{
    private bool $logo = true;

    private bool $media = true;

    private bool $description = true;

    private bool $code = true;

    private array $fields = [];

    private array $custom = [];

    public function __construct(
        private string $store,
        private string $variant
    ) {
    }

    public static function make(string $store, string $variant): static
    {
        return new static($store, $variant);
    }

    public function withoutLogo(): static
    {
        $this->logo = false;

        return $this;
    }

    public function withoutMedia(): static
    {
        $this->media = false;

        return $this;
    }

    public function withoutDescription(): static
    {
        $this->description = false;

        return $this;
    }

    public function withoutCode(): static
    {
        $this->code = false;

        return $this;
    }

    public function withName(string $name): static
    {
        $this->fields['name'] = $name;

        return $this;
    }

    public function withEmail(string $email): static
    {
        $this->fields['email'] = $email;

        return $this;
    }

    public function withBillingAddress(string $country, string $state = null, string $zip = null): static
    {
        $this->fields['billing_address'] = array_filter([
            'country' => $country,
            'state' => $state,
            'zip' => $zip,
        ]);

        return $this;
    }

    public function withTaxNumber(string $taxNumber): static
    {
        $this->fields['tax_number'] = $taxNumber;

        return $this;
    }

    public function withDiscountCode(string $discountCode): static
    {
        $this->fields['discount_code'] = $discountCode;

        return $this;
    }

    public function withCustomData(array $custom): static
    {
        if (
            (array_key_exists('billable_id', $custom) && isset($this->custom['billable_id'])) ||
            (array_key_exists('billable_type', $custom) && isset($this->custom['billable_type'])) ||
            (array_key_exists('subscription_type', $custom) && isset($this->custom['subscription_type']))
        ) {
            throw ReservedCustomKeys::overwriteAttempt();
        }

        $this->custom = collect(array_replace_recursive($this->custom, $custom))
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => ! is_null($value))
            ->toArray();

        return $this;
    }

    public function url(): string
    {
        $params = collect(['logo', 'media', 'description', 'code'])
            ->filter(fn ($toggle) => ! $this->{$toggle})
            ->mapWithKeys(fn ($toggle) => [$toggle => 0]);

        if ($this->fields) {
            $params = $params->merge(array_filter($this->fields));
        }

        if ($this->custom) {
            $params['checkout'] = ['custom' => $this->custom];
        }

        $params = $params->isNotEmpty() ? '?'.http_build_query($params->all()) : '';

        return "https://{$this->store}.lemonsqueezy.com/checkout/buy/{$this->variant}".$params;
    }

    public function redirect(): RedirectResponse
    {
        return Redirect::to($this->url(), 303);
    }

    public function toResponse($request): RedirectResponse
    {
        return $this->redirect();
    }
}
