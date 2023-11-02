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

Lemon Squeezy for Laravel is maintained by [Dries Vints](https://twitter.com/driesvints). Any sponsorship to [help fund development](https://github.com/sponsors/driesvints) is greatly appreciated ❤️

We also recommend to read the Lemon Squeezy [docs](https://docs.lemonsqueezy.com/help) and [developer guide](https://docs.lemonsqueezy.com/guides/developer-guide).

## Roadmap

The below features are not yet in this package but are planned to be added in the future:

- Subscription invoices
- Metered Billing
- License keys
- Marketing emails check
- Product & variant listing
- Create discount codes
- Nova integration

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher

## Installation

There are a few steps you'll need to take to install the package:

1. Requiring the package through Composer
2. Creating an API Key
3. Connecting your store
4. Configuring the Billable Model
5. Running Migrations
6. Connecting to Lemon JS
7. Setting up webhooks

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

Your store identifier will be used when creating checkouts for your products. Go to [your Lemon Squeezy stores settings](https://app.lemonsqueezy.com/settings/stores) and copy the Store ID (the part after the `#` sign) into the env value below:

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

Now your user model will have access to methods from our package to create checkouts in Lemon Squeezy for your products. Note that you can make any model type a billable as you wish. It's not required to use one specific model class.

### Running Migrations

The package comes with some migrations to store data received from Lemon Squeezy by webhooks. It'll add a `lemon_squeezy_customers` table which holds all info about a customer. This table is connected to a billable model of any model type you wish. It'll also add a `lemon_squeezy_subscriptions` table which holds info about subscriptions. Install these migrations by simply running `artisan migrate`:

```bash
php artisan migrate
```

If you want to customize these migrations, you can [overwrite them](#overwriting-migrations).

### Lemon JS

Lemon Squeezy uses its own JavaScript library to initiate its checkout widget. We can make use of it by loading it through the Blade directive in the `head` section of our app, right before the closing `</head>` tag.

```blade
<head>
    ...
 
    @lemonJS
</head>
```

### Webhooks

Finally, make sure to set up incoming webhooks. This is both needed in development as in production. Go to [your Lemon Squeezy's webhook settings](https://app.lemonsqueezy.com/settings/webhooks) and point the url to your exposed local app. You can use [Ngrok](https://ngrok.com/), [Expose](https://github.com/beyondcode/expose) or another tool of your preference for this. Laravel also has solutions for sharing your site with [Valet](https://laravel.com/docs/valet#sharing-sites), [Sail](https://laravel.com/docs/10.x/sail#sharing-your-site) and [Herd](https://herd.laravel.com).

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

By default, we don't recommend publishing the config file as most things can be configured with environment variables. Should you still want to adjust the config file, you can publish it with the following command:

```bash
php artisan vendor:publish --tag="lemon-squeezy-config"
```

### Verifying Webhook Signatures

In order to make sure that incoming webhooks are actually from Lemon Squeezy, we can configure a signing secret for them. Go to your webhook settings in the Lemon Squeezy dashboard, click on the webhook of your app and copy the signing secret into the environment variable below:

```ini
LEMON_SQUEEZY_SIGNING_SECRET=your-webhook-signing-secret
```

Any incoming webhook will now first be verified before being executed.

### Overwriting Migrations

Lemon Squeezy for Laravel ships with some migrations to hold data sent over. If you're using something like a string based identifier for your billable model, like a UUID, or want to adjust something to the migrations you can overwrite them. First, publish these with the following command:

```bash
php artisan vendor:publish --tag="lemon-squeezy-migrations"
```

Then, ignore the package's migrations in your `AppServiceProvider`'s `register` method:

```php
use LemonSqueezy\Laravel\LemonSqueezy;

public function register(): void
{
    LemonSqueezy::ignoreMigrations();
}
```

Now you'll rely on your own migrations rather than the package one. Please note though that you're now responsible as well for keeping these in sync withe package one manually whenever you upgrade the package.

## Checkouts

With this package, you can easily create checkouts for your customers.

### Single Payments

For example, to create a checkout for a single-payment, use a variant ID of a product variant you want to sell and create a checkout using the snippet below:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return $request->user()->checkout('variant-id');
});
```

This will automatically redirect your customer to a Lemon Squeezy checkout where the customer can buy your product.

> **Note**
> When creating a checkout for your store, each time you redirect a checkout object or call `url` on the checkout object, an API call to Lemon Squeezy will be made. These calls are expensive and can be time and resource consuming for your app. If you are creating the same session over and over again you may want to cache these urls. 

#### Custom Priced Charges

You can also overwrite the amount of a product variant by calling the `charge` method on a customer:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return $request->user()->charge(2500, 'variant-id');
});
```

The amount should be a positive integer in cents.

You'll still need to provide a variant ID but can overwrite the price as you see fit. One thing you can do is create a "generic" product with a specific currency which you can dynamically charge against.

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

When a user clicks this button, it'll trigger the Lemon Squeezy checkout overlay. You can also, optionally request it to be rendered in dark mode:

```blade
<x-lemon-button :href="$checkout" class="px-8 py-4" dark>
    Buy Product
</x-lemon-button>
```

If you're checking out subscriptions, and you don't want to show the "You will be charged..." text, you may disable this by calling the `withoutSubscriptionPreview` method on the checkout object:

```php
$request->user()->subscribe('variant-id')
    ->withoutSubscriptionPreview();
```

If you want to set a different color for the checkout button you may pass a hex color code (with the leading `#` sign) through `withButtonColor`:

```php
$request->user()->checkout('variant-id')
    ->withButtonColor('#FF2E1F');
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
    return $request->user()->checkout('variant-id')
        ->withName('John Doe')
        ->withEmail('john@example.com')
        ->withBillingAddress('US', '10038') // Country & Zip Code
        ->withTaxNumber('123456679')
        ->withDiscountCode('PROMO');
});
```

### Product Details

You can overwrite additional data for product checkouts with the `withProductName` and `withDescription` methods:

```php
$request->user()->checkout('variant-id')
    ->withProductName('Ebook')
    ->withDescription('A thrilling novel!');
```

### Receipt Thank You

Additionally, you can customize the thank you note for the order receipt email.

```php
$request->user()->checkout('variant-id')
    ->withThankYouNote('Thanks for your purchase!');
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

In order to do this you'll need to [publish your config file](#configuration).

### Expire Checkouts

You can indicate how long a checkout session should stay active by calling the `expiresAt` method on it:

```php
$request->user()->checkout('variant-id')
    ->expiresAt(now()->addDays(3));
```

### Custom Data

You can also [pass along custom data with your checkouts](https://docs.lemonsqueezy.com/help/checkout/passing-custom-data). To do this, send along key/value pairs with the checkout method:

```php
use Illuminate\Http\Request;
 
Route::get('/buy', function (Request $request) {
    return $request->user()->checkout('variant-id', custom: ['foo' => 'bar']);
});
```

These will then later be available in the related webhooks for you.

#### Reserved Keywords

When working with custom data there are a few reserved keywords for this library:

- `billable_id`
- `billable_type`
- `subscription_type`

Attempting to use any of these will result in an exception being thrown.

## Customers

### Customer Portal

Customers may easily manage their personal data like their name, email address, etc by visiting their [customer portal](https://docs.lemonsqueezy.com/guides/developer-guide/customer-portal). Lemon Squeezy for Laravel makes it easy to redirect customers to this by calling `redirectToCustomerPortal` on the billable:

```php
use Illuminate\Http\Request;
 
Route::get('/customer-portal', function (Request $request) {
    return $request->user()->redirectToCustomerPortal();
});
```

In order to call this method your billable already needs to have a subscription or made a purchase through Lemon Squeezy. Also, this method will perform an underlying API call so make sure to place this redirect behind a route which you can link to in your app.

Optionally, you also get the signed customer portal url directly:

```php
$url = $user->customerPortalUrl();
```

## Subscriptions

### Setting Up Subscription Products

Setting up subscription products with different plans and intervals needs to be done in a specific way. Lemon Squeezy has [a good guide](https://docs.lemonsqueezy.com/guides/tutorials/saas-subscription-plans) on how to do this.

Although you're free to choose how you set up products and plans, it's easier to go for option two and create a product for each plan type. So for example, when you have a "Basic" and "Pro" plan and both have monthly and yearly prices, it's wiser to create two separate products for these and then add two variants for each for their monthly and yearly prices.

This gives you the advantage later on to make use of the `hasProduct` method on a subscription which allows you to just check if a subscription is on a specific plan type and don't worry if it's on a monthly or yearly schedule.

### Creating Subscriptions

Starting subscriptions is easy. For this, we need the variant id from our product. Copy the variant id and initiate a new subscription checkout from your billable model:

```php
use Illuminate\Http\Request;
 
Route::get('/subscribe', function (Request $request) {
    return $request->user()->subscribe('variant-id');
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

This method, as well as the `subscribed` method, will return true if your subscription is active, on trial, past due, paused for free or on its cancelled grace period.

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

If a recurring payment for a subscription fails, the subscription will transition in a past due state. This means it's still a valid subscription but your customer will have a 2 weeks period where their payments will be retried.

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

    return view('billing', [
        'paymentMethodUrl' => $subscription->updatePaymentMethodUrl(),
    ]);
});
```

Alternatively, if you want the URL to open in a more seamless way on top of your app (similar to the checkout overlay), you may use [Lemon.js](https://docs.lemonsqueezy.com/help/lemonjs/opening-overlays#updating-payment-details-overlay) to open the URL with the `LemonSqueezy.Url.Open()` method. First, pass the url to a view:

```php
use Illuminate\Http\Request;
 
Route::get('/update-payment-info', function (Request $request) {
    $subscription = $request->user()->subscription();

    return view('billing', [
        'paymentMethodUrl' => $subscription->updatePaymentMethodUrl(),
    ]);
});
```

Then trigger it through a button:

```blade
<script defer>
    function updatePM() {
        LemonSqueezy.Url.Open('{!! $paymentMethodUrl !!}');
    }
</script>

<button onclick="updatePM()">
    Update payment method
</button>
```

This requires you to have set up [Lemon.js](#lemon-js).

### Changing Plans

When a customer is subscribed to a monthly plan, they might want to upgrade to a better plan, change their payments to a yearly plan or downgrade to a cheaper plan. For these situations, you can allow them to swap plans by passing a different variant id with its product id to the `swap` method:

```php
use App\Models\User;

$user = User::find(1);

$user->subscription()->swap('product-id', 'variant-id');
```

This will swap the customer to their new subscription plan but billing will only be done on the next billing cycle. If you'd like to immediately invoice the customer you may use the `swapAndInvoice` method instead:

```php
$user = User::find(1);

$user->subscription()->swapAndInvoice('product-id', 'variant-id');
```

> **Note**
> You'll notice in the above methods that you both need to provide a product ID and variant ID and might wonder why that is. Can't you derive the product ID from the variant ID? Unfortuntately that's only possible when swapping to variants between the same product. When swapping to a different product alltogether you are required to also provide the product ID in the Lemon Squeezy API. Therefor, we've made the decision to make this uniform and just always require the product ID as well.

#### Prorations

By default, Lemon Squeezy will prorate amounts when changing plans. If you want to prevent this, you may use the `noProrate` method before executing the swap:

```php
$user = User::find(1);

$user->subscription()->noProrate()->swap('product-id', 'variant-id');
```

### Changing The Billing Date

To change the date of the month on which your customer gets billed for their subscription, you may use the `anchorBillingCycleOn` method:

```php
$user = User::find(1);

$user->subscription()->anchorBillingCycleOn(21);
```

In the above example, the customer will now get billed on the 21st of each month going forward. For more info, see [the Lemon Squeezy docs](https://docs.lemonsqueezy.com/guides/developer-guide/managing-subscriptions#changing-the-billing-date).

### Multiple Subscriptions

In some situation you may find yourself wanting to allow your customer to subscribe to multiple subscription types. For example, a gym may offer a swimming and weight lifting subscription. You can allow your customer to subscribe to either or both.

To handle the different subscriptions you may provide a `type` of subscription as the second argument to `subscribe` when starting a new one:

```php
$user = User::find(1);

$checkout = $user->subscribe('variant-id', 'swimming');
```

Now you may always refer this specific subscription type by providing the `type` argument when retrieving it:

```php
$user = User::find(1);

// Retrieve the swimming subscription type...
$subscription = $user->subscription('swimming');

// Swap plans for the gym subscription type...
$user->subscription('gym')->swap('product-id', 'variant-id');

// Cancel the swimming subscription...
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

For a thorough read on trialing in Lemon Squeezy, [have a look at their guide](https://docs.lemonsqueezy.com/guides/tutorials/saas-free-trials).

#### No Payment Required

To allow people to signup for your product without having them to fill out their payment details, you may set the `trial_ends_at` column when creating them as a customer:

```php
use App\Models\User;
 
$user = User::create([
    // ...
]);
 
$user->createAsCustomer([
    'trial_ends_at' => now()->addDays(10)
]);
```

This is what's called "a generic trial" because it's not attached to any subscription. You can use the `onTrial` method to check if a customer is currently trialing your app:

```php
if ($user->onTrial()) {
    // User is within their trial period...
}
```

Or if you specifically also want to make sure it's a generic trial, you can use the `onGenericTrial` method:

```php
if ($user->onGenericTrial()) {
    // User is within their "generic" trial period...
}
```

You can also retrieve the ending date of the trial by calling the `trialEndsAt` method:

```php
if ($user->onTrial()) {
    $trialEndsAt = $user->trialEndsAt();
}
```

As soon as your customer is ready, or after their trial has expired, they may start their subscription:

```php
use Illuminate\Http\Request;

Route::get('/buy', function (Request $request) {
    return $request->user()->subscribe('variant-id');
});
```

Please note that when a customer starts their subscription when they're still on their generic trial, their trial will be cancelled because they have started to pay for your product.

#### Payment required

Another option is to require payment details when people want to trial your products. This means that after the trial expires, they'll immediately be subscribed to your product. To get started with this, you'll need to [configure a trial period in your product's settings](https://docs.lemonsqueezy.com/guides/tutorials/saas-free-trials#1-create-subscription-products-with-trials). Then, let a customer start a subscription:

```php
use Illuminate\Http\Request;

Route::get('/buy', function (Request $request) {
    return $request->user()->subscribe('variant-id');
});
```

After your customer is subscribed, they'll enter their trial period which you configured and won't be charged until after this date. You'll need to give them the option to cancel their subscription before this time if they want.

To check if your customer is currently on their free trial, you may use the `onTrial` method on both the billable or an individual subscription:

```php
if ($user->onTrial()) {
    // ...
}
 
if ($user->subscription()->onTrial()) {
    // ...
}
```

To determine if a trial has expired, you may use the `hasExpiredTrial` method:

```php
if ($user->hasExpiredTrial()) {
    // ...
}
 
if ($user->subscription()->hasExpiredTrial()) {
    // ...
}
```

##### Ending Trials Early

To end a trial with payment upfront early you may use the `endTrial` method on a subscription:

```php
$user = User::find(1);

$user->subscription()->endTrial();
```

This method will move the billing achor to the current day and thus ending any trial period the customer had.

## Handling Webhooks

Lemon Squeezy can send your app webhooks which you can react on. By default, this package already does the bulk of the work for you. [If you've properly set up webhooks](#webhooks), it'll listen to any incoming events and update your database accordingly. We recommend enabling all event types so it's easy for you to upgrade in the future.

To listen to incoming webhooks, we have two events that will be fired:

- `LemonSqueezy\Laravel\Events\WebhookReceived`
- `LemonSqueezy\Laravel\Events\WebhookHandled`

The `WebhookReceived` will be fired as soon as a webhook comes in but has not been handled by the package's `WebhookController`. The `WebhookHandled` event will be fired as soon as the webhook has been processed by the package. Both events will contain the full payload of the incoming webhook.

If you want to react to these events, you'll have to create listeners for them. For example, you may want to react to a subscription being updated:

```php
<?php
 
namespace App\Listeners;
 
use LemonSqueezy\Laravel\Events\WebhookHandled;
 
class LemonSqueezyEventListener
{
    /**
     * Handle received Lemon Squeezy webhooks.
     */
    public function handle(WebhookHandled $event): void
    {
        if ($event->payload['meta']['event_name'] === 'subscription_updated') {
            // Handle the incoming event...
        }
    }
}
```

For an example payload, [take a look at the Lemon Squeezy API docs](https://docs.lemonsqueezy.com/api/webhooks#webhook-requests). 

Once you have a listener, wire it up in your app's `EventServiceProvider`:

```php
<?php
 
namespace App\Providers;
 
use App\Listeners\LemonSqueezyEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use LemonSqueezy\Laravel\Events\WebhookHandled;
 
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        WebhookHandled::class => [
            LemonSqueezyEventListener::class,
        ],
    ];
}
```

### Webhook Events

Instead of listening to the `WebhookHandled` event, you may also subscribe to one of the following, dedicated package events that are fired after a webhook has been handled:

- `LemonSqueezy\Laravel\Events\OrderCreated`
- `LemonSqueezy\Laravel\Events\OrderRefunded`
- `LemonSqueezy\Laravel\Events\SubscriptionCreated`
- `LemonSqueezy\Laravel\Events\SubscriptionUpdated`
- `LemonSqueezy\Laravel\Events\SubscriptionCancelled`
- `LemonSqueezy\Laravel\Events\SubscriptionResumed`
- `LemonSqueezy\Laravel\Events\SubscriptionExpired`
- `LemonSqueezy\Laravel\Events\SubscriptionPaused`
- `LemonSqueezy\Laravel\Events\SubscriptionUnpaused`
- `LemonSqueezy\Laravel\Events\SubscriptionPaymentSuccess`
- `LemonSqueezy\Laravel\Events\SubscriptionPaymentFailed`
- `LemonSqueezy\Laravel\Events\SubscriptionPaymentRecovered`
- `LemonSqueezy\Laravel\Events\LicenseKeyCreated`
- `LemonSqueezy\Laravel\Events\LicenseKeyUpdated`

All of these events contain a billable `$model` instance and the event `$payload`. The subscription events also contain the `$subscription` object. These can be accessed through their public properties.

## Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

## License

Lemon Squeezy for Laravel is open-sourced software licensed under [the MIT license](LICENSE.md).
