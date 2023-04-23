<?php

namespace LemonSqueezy\Laravel;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use LemonSqueezy\Laravel\Exceptions\ReservedCustomKeys;

class Checkout implements Responsable
{
    private bool $logo = true;

    private bool $media = true;

    private bool $desc = true;

    private bool $discount = true;

    private array $checkoutData = [];

    private array $custom = [];

    private ?string $redirectUrl;

    public function __construct(private string $store, private string $variant)
    {
    }

    public static function make(string $store, string $variant): static
    {
        return new static($store, $variant);
    }

    public function withoutLogo(): self
    {
        $this->logo = false;

        return $this;
    }

    public function withoutMedia(): self
    {
        $this->media = false;

        return $this;
    }

    public function withoutDescription(): self
    {
        $this->desc = false;

        return $this;
    }

    public function withoutDiscountField(): self
    {
        $this->discount = false;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->checkoutData['name'] = $name;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->checkoutData['email'] = $email;

        return $this;
    }

    public function withBillingAddress(string $country, string $zip = null): self
    {
        $this->checkoutData['billing_address'] = array_filter([
            'country' => $country,
            'zip' => $zip,
        ]);

        return $this;
    }

    public function withTaxNumber(string $taxNumber): self
    {
        $this->checkoutData['tax_number'] = $taxNumber;

        return $this;
    }

    public function withDiscountCode(string $discountCode): self
    {
        $this->checkoutData['discount_code'] = $discountCode;

        return $this;
    }

    public function withCustomData(array $custom): self
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

    public function redirectTo(string $url): self
    {
        $this->redirectUrl = $url;

        return $this;
    }

    public function url(): string
    {
        $response = LemonSqueezy::api('POST', 'checkouts', [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'checkout_data' => array_merge(
                        array_filter($this->checkoutData, fn ($value) => $value !== ''),
                        ['custom' => $this->custom]
                    ),
                    'checkout_options' => [
                        'logo' => $this->logo,
                        'media' => $this->media,
                        'desc' => $this->desc,
                        'discount' => $this->discount,
                    ],
                    'product_options' => [
                        'redirect_url' => $this->redirectUrl ?? config('lemon-squeezy.redirect_url'),
                    ],
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => $this->store,
                        ],
                    ],
                    'variant' => [
                        'data' => [
                            'type' => 'variants',
                            'id' => $this->variant,
                        ],
                    ],
                ],
            ],
        ]);

        return $response['data']['attributes']['url'];
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
