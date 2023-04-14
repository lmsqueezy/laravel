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

A package to easily integrate your [Laravel](https://laravel.com) application with Lemon Squeezy.

This package drew inspiration from [Cashier](https://github.com/laravel/cashier-stripe) which was created by [Taylor Otwell](https://twitter.com/taylorotwell).

Lemon Squeezy for Laravel is maintained by [Dries Vints](https://twitter.com/driesvints). Any sponsorship to [help fund development on this package](https://github.com/sponsors/driesvints) is greatly appreciated ❤️

> This package is a work in progress. As long as there is no v1.0.0, breaking changes may occur in v0.x releases.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher

## Installation

Install the package with composer:

```bash
composer require lemonsqueezy/laravel
```

Next, make sure to set up incoming webhooks. This is both needed in development as in production. Go to [your Lemon Squeezy's webhook settings](https://app.lemonsqueezy.com/settings/webhooks) and point the url to your exposed local app. You can use [Ngrok](https://ngrok.com/), [Expose](https://github.com/beyondcode/expose) or another tool of your preference for this.

Make sure to select all event types. The path you should point to is `/lemon-squeezy/webhook` by default. 

### Webhooks & CSRF Protection

Incoming webhooks should not be affected by [CSRF protection](https://laravel.com/docs/csrf). To prevent this, add your webhook path to the except list of your `App\Http\Middleware\VerifyCsrfToken` middleware:

```php
protected $except = [
    'lemon-squeezy/*',
];
```
