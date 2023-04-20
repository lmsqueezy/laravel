<p align="center"><img src="https://github.com/lmsqueezy/laravel/raw/HEAD/art/readme-header.png" alt="Readme header"></p>

# Lemon Squeezy for Laravel

<a href="https://github.com/lmsqueezy/laravel/actions">
    <img src="https://github.com/lmsqueezy/laravel/actions/workflows/tests.yml/badge.svg" alt="Tests">
</a>
<a href="https://github.com/lmsqueezy/laravel/actions/workflows/coding-standards.yml">
    <img src="https://github.com/lmsqueezy/laravel/actions/workflows/coding-standards.yml/badge.svg" alt="Coding Standards" />
</a>
<a href="https://packagist.org/packages/lemonsqueezy/laravel">
    <img src="https://img.shields.io/packagist/v/lemonsqueezy/laravel" alt="Latest Stable Version">
</a>
<a href="https://packagist.org/packages/lemonsqueezy/laravel">
    <img src="https://img.shields.io/packagist/dt/lemonsqueezy/laravel" alt="Total Downloads">
</a>

A package to easily integrate your [Laravel](https://laravel.com) application with Lemon Squeezy. It takes the pain out of setting up a checkout experience. Easily set up payments for your products or let your customers subscribe to your product plans. Handle grace periods, pause subscriptions, or offer free trials.

This package drew inspiration from [Cashier](https://github.com/laravel/cashier-stripe) which was created by [Taylor Otwell](https://twitter.com/taylorotwell).

Lemon Squeezy for Laravel is maintained by [Dries Vints](https://twitter.com/driesvints). Any sponsorship to [help fund development off this package](https://github.com/sponsors/driesvints) is greatly appreciated ❤️

We also recommend to read the Lemon Squeezy [docs](https://docs.lemonsqueezy.com/help) and [developer guide](https://docs.lemonsqueezy.com/guides/developer-guide).

> This package is a work in progress. As long as there is no v1.0.0, breaking changes may occur in v0.x releases. No upgrade path between v0.x versions will be provided.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher

## Installation

There are a few steps you'll need to take to install the package:

1. Requiring the package through Composer
2. Creating an API Key
3. Connecting your store
4. Configuring the Billable Model
5. Connecting to Lemon JS
6. Setting up webhooks

We'll go over each of these below.

### Composer

Install the package with composer:

```bash
composer require lemonsqueezy/laravel
```

### API Key

Next, configure your API key. Create a new key in testing mode in [the Lemon Squeezy dashboard](https://app.lemonsqueezy.com/settings/api) and paste them in your `.env` file as shown below:

```ini
LEMON_SQUEEZY_API_KEY=your-lemon-squeezy-api-key
```

When you're deploying your app to production, you'll have to create a new key in production mode to work with live data.

### Store URL

Your store url will be used when creating checkouts for your products. Go to [your Lemon Squeezy general settings](https://app.lemonsqueezy.com/settings/general) and copy the Store URL subdomain (the part before `.lemonsqueezy.com`) into the env value below:

```ini
LEMON_SQUEEZY_STORE=your-lemon-squeezy-subdomain
```

### Billable Model

To make sure we can actually create checkouts for our customers, we'll need to configure a model to be our "billable" model. This is typical the `User` model of your app. To do this, import and use the `Billable` trait on your model:

```php
use LemonSqueezy\Laravel\Billable;
 
class User extends Authenticatable
{
    use Billable;
}
```

Now your user model will have access to methods from our package to create checkouts in Lemon Squeezy for your products.

### Lemon JS

Lemon Squeezy uses its own JavaScript library to initiate its checkout widget. We can make use of it by loading it through the Blade directive in the `head` section of our app, right before the closing `</head>` tag.

```blade
<head>
    ...
 
    @lemonJS
</head>
```

### Webhooks

Finally, make sure to set up incoming webhooks. This is both needed in development as in production. Go to [your Lemon Squeezy's webhook settings](https://app.lemonsqueezy.com/settings/webhooks) and point the url to your exposed local app. You can use [Ngrok](https://ngrok.com/), [Expose](https://github.com/beyondcode/expose) or another tool of your preference for this.

Make sure to select all event types. The path you should point to is `/lemon-squeezy/webhook` by default. **We also very much recommend to [verify webhook signatures](#verifying-webhook-signatures).**

#### Webhooks & CSRF Protection

Incoming webhooks should not be affected by [CSRF protection](https://laravel.com/docs/csrf). To prevent this, add your webhook path to the except list of your `App\Http\Middleware\VerifyCsrfToken` middleware:

```php
protected $except = [
    'lemon-squeezy/*',
];
```

## Upgrading

Please review [our upgrade guide](./UPGRADE.md) when upgrading to a new version.

## Configuration

The package offers various way to configure your experience with integrating with Lemon Squeezy.

### Verifying Webhook Signatures

In order to make sure that incoming webhooks are actually from Lemon Squeezy, we can configure a signing secret for them. Go to your webhook settings in the Lemon Squeezy dashboard, click on the webhook of your app and copy the signing secret into the environment variable below:

```ini
LEMON_SQUEEZY_SIGNING_SECRET=your-webhook-signing-secret
```

Any incoming webhook will now first be verified before being executed.

## Checkouts

With this package, you can easily create checkouts for your customers.

### Single Payments

For example, to create a checkout for a single-payment, click the "share" button of the product you want to share, copy its UUID from the share url and create a checkout using the snippet below:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return response()->redirect(
        $request->user()->checkout('your-product-uuid')
    );
});
```

This will automatically redirect your customer to a Lemon Squeezy checkout where the customer can buy your product. 

> **Note**  
> Please note that the product UUID is not the same as the variant ID.

### Overlay Widget

Instead of redirecting your customer to a checkout screen, you can also create a checkout button which will render a checkout overlay on your page. To do this, pass the `$checkout` object to a view:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    $checkout = $request->user()->checkout('your-product-uuid');

    return view('billing', ['checkout' => $checkout]);
});
```

Now, create the button using the shipped Laravel Blade component from the package:

```blade
<x-lemon-button :href="$checkout" class="px-8 py-4">
    Buy Product
</x-lemon-button>
```

When a user clicks this button, it'll trigger the Lemon Squeezy checkout overlay. You can also, optionally request it to be rendered in dark mode:

```blade
<x-lemon-button :href="$checkout" class="px-8 py-4" dark>
    Buy Product
</x-lemon-button>
```

### Prefill User Data

You can easily prefill user data for checkouts by overwriting the following methods on your billable model:

```php
public function lemonSqueezyName(): ?string; // name
public function lemonSqueezyEmail(): ?string; // email
public function lemonSqueezyCountry(): ?string; // country
public function lemonSqueezyState(): ?string; // state
public function lemonSqueezyZip(): ?string; // zip
public function lemonSqueezyTaxNumber(): ?string; // tax_number
```

By default, the attributes displayed in a comment on the right of the methods will be used.

Additionally, you may also pass this data on the fly by using the following methods:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return response()->redirect(
        $request->user()->checkout('your-product-uuid')
            ->withName('John Doe')
            ->withEmail('john@example.com')
            ->withBillingAddress('US', 'NY', '10038')
            ->withTaxNumber('123456679')
            ->withDiscountCode('PROMO')
    );
});
```

### Redirects After Purchase

After a purchase the customer will be redirected to your Lemon Squeezy's store. If you want them to be redirected back to your app, you'll have to configure the url in your settings in your Lemon Squeezy dashboard for each individual product.

### Custom Data

You can also [pass along custom data with your checkouts](https://docs.lemonsqueezy.com/help/checkout/passing-custom-data). To do this, send along key/value pairs with the checkout method:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return response()->redirect(
        $request->user()->checkout('your-product-uuid', custom: ['foo' => 'bar'])
    );
});
```

These will then later be available in the related webhooks for you.

#### Reserved Keywords

When working with custom data there are a few reserved keywords for this library:

- `billable_id`
- `billable_type`
- `subscription_type`

Attempting to use any of these will result in an exception being thrown.

## Subscriptions

### Setting Up Subscription Products

Setting up subscription products with different plans and intervals needs to be done in a specific way. Lemon Squeezy has [a good guide](https://docs.lemonsqueezy.com/guides/tutorials/saas-subscription-plans) on how to do this.

Although you're free to choose how you set up products and plans, it's easier to go for option two and create a product for each plan type. So for example, when you have a "Basic" and "Pro" plan and both have monthly and yearly prices, it's wiser to create two separate products for these and then add two variants for each for their monthly and yearly prices.

This gives you the advantage later on to make use of the `hasProduct` method on a subscription which allows you to just check if a subscription is on a specific plan type and don't worry if it's on a monthly or yearly schedule.

### Creating Subscriptions

Starting subscriptions is easy. For this, we need the UUID from our product. Click the "share" button of the subscription product you want to share, copy its UUID from the share url and initiate a new subscription checkout from your billable model:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return response()->redirect(
        $request->user()->subscribe('your-product-uuid')
    );
});
```

When the customer has finished their checkout, the incoming `SubscriptionCreated` webhook will couple it to your billable model in the database. You can then retrieve the subscription from your billable model:

```php
$subscription = $user->subscription();
```

### Checking Subscription Status

Coming soon...

#### Subscription Scopes

Various subscriptions scopes are available to query subscriptions in specific states:

```php
// Get all active subscriptions...
$subscriptions = Subscription::query()->active()->get();
 
// Get all of the cancelled subscriptions for a specific user...
$subscriptions = $user->subscriptions()->cancelled()->get();
```

Here's all available scopes:

```php
Subscription::query()->onTrial();
Subscription::query()->active();
Subscription::query()->paused();
Subscription::query()->pastDue();
Subscription::query()->unpaid();
Subscription::query()->cancelled();
Subscription::query()->expired();
```

### Updating Payment Information

To allow your customer to [update their payment details](https://docs.lemonsqueezy.com/guides/developer-guide/managing-subscriptions#updating-billing-details), like their credit card info, you can redirect them with the following method:

```php
use Illuminate\Http\Request;
 
Route::get('/update-payment-info', function (Request $request) {
    $subscription = $request->user()->subscription();

    return response()->redirect(
        $subscription->updatePaymentMethodUrl()
    );
});
```

To make the URL open in a more seamless overlay on top of your app (similar to the checkout overlay), you may use [Lemon.js](https://docs.lemonsqueezy.com/help/lemonjs/opening-overlays#updating-payment-details-overlay) to open the URL with the `LemonSqueezy.Url.Open()` method.

### Changing Plans

Coming soon...

### Multiple Subscriptions

Coming soon...

### Pausing Subscriptions

Coming soon...

### Cancelling Subscriptions

Coming soon...

### Subscription Trials

Coming soon...

## Handling Webhooks

Coming soon...
