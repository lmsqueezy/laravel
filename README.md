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

### Store Identifier

Your store identifier will be used when creating checkouts for your products. Go to [your Lemon Squeezy general settings](https://app.lemonsqueezy.com/settings/general) and copy the Store ID (the part after the `#` sign) into the env value below:

```ini
LEMON_SQUEEZY_STORE=your-lemon-squeezy-store-id
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

For example, to create a checkout for a single-payment, use a variant ID of a product variant you want to sell and create a checkout using the snippet below:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return redirect(
        $request->user()->checkout('variant-id')
    );
});
```

This will automatically redirect your customer to a Lemon Squeezy checkout where the customer can buy your product.

> **Note**
> When creating a checkout for your store, each time you redirect a checkout object or call `url` on the checkout object, an API call to Lemon Squeezy will be made. These calls are expensive and can be time and resource consuming for your app. If you are creating the same session over and over again you may want to cache these urls. 

### Overlay Widget

Instead of redirecting your customer to a checkout screen, you can also create a checkout button which will render a checkout overlay on your page. To do this, pass the `$checkout` object to a view:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    $checkout = $request->user()->checkout('variant-id');

    return view('billing', ['checkout' => $checkout]);
});
```

Now, create the button using the shipped Laravel Blade component from the package:

```blade
<x-lemon-button :href="$checkout" class="px-8 py-4">
    Buy Product
</x-lemon-button>
```

### Prefill User Data

You can easily prefill user data for checkouts by overwriting the following methods on your billable model:

```php
public function lemonSqueezyName(): ?string; // name
public function lemonSqueezyEmail(): ?string; // email
public function lemonSqueezyCountry(): ?string; // country
public function lemonSqueezyZip(): ?string; // zip
public function lemonSqueezyTaxNumber(): ?string; // tax_number
```

By default, the attributes displayed in a comment on the right of the methods will be used.

Additionally, you may also pass this data on the fly by using the following methods:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return redirect(
        $request->user()->checkout('variant-id')
            ->withName('John Doe')
            ->withEmail('john@example.com')
            ->withBillingAddress('US', '10038') // Country & Zip Code
            ->withTaxNumber('123456679')
            ->withDiscountCode('PROMO')
    );
});
```

### Redirects After Purchase

To redirect customers back to your app after purchase, you may use the `redirectTo` method:

```php
$request->user()->checkout('variant-id')
    ->redirectTo(url('/'));
```

You may also set a default url for this by configuring the `lemon-squeezy.redirect_url` in your config file:

```php
'redirect_url' => 'https://my-app.com/dashboard',
```

### Custom Data

You can also [pass along custom data with your checkouts](https://docs.lemonsqueezy.com/help/checkout/passing-custom-data). To do this, send along key/value pairs with the checkout method:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return redirect(
        $request->user()->checkout('variant-id', custom: ['foo' => 'bar'])
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

Starting subscriptions is easy. For this, we need the variant id from our product. Copy the variant id and initiate a new subscription checkout from your billable model:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return redirect(
        $request->user()->subscribe('variant-id')
    );
});
```

When the customer has finished their checkout, the incoming `SubscriptionCreated` webhook will couple it to your billable model in the database. You can then retrieve the subscription from your billable model:

```php
$subscription = $user->subscription();
```

### Checking Subscription Status

Once a customer is subscribed to your services, you can use a variety of methods to check for various states on the subscription. The most basic example, is to check if a customer is subscribed to a valid subscription:

```php
if ($user->subscribed()) {
    // ...
}
```

You may use this in various places in your app like middleware, policies, etc, to offer your services. To check if an individual subscription is valid, you may use the `valid` method:

```php
if ($user->subscription()->valid()) {
    // ...
}
```

This state will return true if your subscription is active, on trial, paused for free or on its cancelled grace period.

You can also check if a subscription is on a specific product:

```php
if ($user->subscription()->hasProduct('your-product-id')) {
    // ...
}
```

Or on a specific variant:

```php
if ($user->subscription()->hasVariant('your-variant-id')) {
    // ...
}
```

If you want to check if a subscription is on a specific variant and at the same valid you can use:

```php
if ($user->subscribedToVariant('your-variant-id')) {
    // ...
}
```

Or if you're using [multiple subscription types](#multiple-subscriptions), you can pass a type as an extra parameter:

```php
if ($user->subscribed('swimming')) {
    // ...
}

if ($user->subscribedToVariant('your-variant-id', 'swimming')) {
    // ...
}
```

#### Cancelled Status

To check if a user has cancelled their subscription you may use the `cancelled` method:

```php
if ($user->subscription()->cancelled()) {
    // ...
}
```

When they're on their grace period, you can use the `onGracePeriod` check:

```php
if ($user->subscription()->onGracePeriod()) {
    // ...
}
```

If a subscription is fully cancelled and no longer on its grace period, you may use the `expired` check:

```php
if ($user->subscription()->expired()) {
    // ...
}
```

#### Past Due Status

If a recurring payment for a subscription fails, the subscription will transition in a past due state. This means it's no longer a valid subscription and won't be active until the customer has updated their payment info and the open invoice has been paid.

```php
if ($user->subscription()->pastDue()) {
    // ...
}
```

In this state, you should instruct your customer to [update their payment info](#updating-payment-information). Failed payments in Lemon Squeezy are retried a couple of times. For more information on that, as well as the dunning process, head over to [the Lemon Squeezy documentation](https://docs.lemonsqueezy.com/help/online-store/recovery-dunning)

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

    return redirect(
        $subscription->updatePaymentMethodUrl()
    );
});
```

To make the URL open in a more seamless overlay on top of your app (similar to the checkout overlay), you may use [Lemon.js](https://docs.lemonsqueezy.com/help/lemonjs/opening-overlays#updating-payment-details-overlay) to open the URL with the `LemonSqueezy.Url.Open()` method.

### Changing Plans

When a customer is subscribed to a monthly plan, they might want to upgrade to a better plan, change their payments to a yearly plan or downgrade to a cheaper plan. For these situations, you can allow them to swap plans by passing a different variant id to the `swap` method:

```php
use App\Models\User;

$user = User::find(1);

$user->subscription()->swap('variant-id');
```

This will swap the customer to their new subscription plan but billing will only be done on the next billing cycle. If you'd like to immediately invoice the customer you may use the `swapAndInvoice` method instead:

```php
$user = User::find(1);

$user->subscription()->swapAndInvoice('variant-id');
```

#### Prorations

By default, Lemon Squeezy will prorate amounts when changing plans. If you want to prevent this, you may use the `noProrate` method before executing the swap:

```php
$user = User::find(1);

$user->subscription()->noProrate()->swap('variant-id');
```

### Multiple Subscriptions

In some situation you may find yourself wanting to allow your customer to subscribe to multiple subscription types. For example, a gym may offer a swimming and weight lifting subscription. You can allow your customer to subscribe to either or both.

To handle the different subscriptions you may provide a `type` of subscription as the second argument when starting a new one:

```php
$user = User::find(1);

$checkout = $user->subscribe('variant-id', 'swimming');
```

Now you may always refer this specific subscription type by providing the `type` argument when retrieving it:

```php
$user = User::find(1);

// Swap plans...
$user->subscription('swimming')->swap('variant-id');

// Cancel...
$user->subscription('swimming')->cancel();
```

### Pausing Subscriptions

To [pause subscriptions](https://docs.lemonsqueezy.com/guides/developer-guide/managing-subscriptions#pausing-and-unpausing-subscriptions), call the `pause` method on it:

```php
$user = User::find(1);

$user->subscription()->pause();
```

Optionally, provide a date when the subscription can resume:

```php
$user = User::find(1);

$user->subscription()->pause(
    now()->addDays(5)
);
```

This will fill in the `resumes_at` timestamp on your customer. To know if your subscription is within its paused period you can use the `onPausedPeriod` method:

```php
if ($user->subscription()->onPausedPeriod()) {
    // ...
}
```

To unpause, simply call that method on the subscription:

```php
$user->subscription()->unpause();
```

#### Pause State

By default, pausing a subscription will void its usage for the remainder of the pause period. If you instead would like your customers to use your services for free, you may use the `pauseForFree` method:

```php
$user->subscription()->pauseForFree();
```

### Cancelling Subscriptions

To [cancel a subscription](https://docs.lemonsqueezy.com/guides/developer-guide/managing-subscriptions#cancelling-and-resuming-subscriptions), call the `cancel` method on it:

```php
$user = User::find(1);

$user->subscription()->cancel();
```

This will set your subscription to be cancelled. If your subscription is cancelled mid-cycle, it'll enter a grace period and the `ends_at` column will be set. The customer will still have access to the services provided for the remainder of the period. You can check for its grace period by calling the `onGracePeriod` method:

```php
if ($user->subscription()->onGracePeriod()) {
    // ...
}
```

Immediate cancellation with Lemon Squeezy is not possible. To resume a subscription while it's still on its grace period, call the `resume` method:

```php
$user->subscription()->resume();
```

When a cancelled subscription reaches the end of its grace period it'll transition to a state of expired and won't be able to resume any longer.

### Subscription Trials

Coming soon...

## Receipts

Coming soon...

## Handling Webhooks

Coming soon...
