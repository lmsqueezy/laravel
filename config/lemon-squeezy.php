<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy API Key
    |--------------------------------------------------------------------------
    |
    | The Lemon Squeezy API key is used to authenticate with the Lemon Squeezy
    | API. You can find your API key in the Lemon Squeezy dashboard. You can
    | find your API key in the Lemon Squeezy dashboard under the "API" section.
    |
    */

    'api_key' => env('LEMON_SQUEEZY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Signing Secret
    |--------------------------------------------------------------------------
    |
    | The Lemon Squeezy signing secret is used to verify that the webhook
    | requests are coming from Lemon Squeezy. You can find your signing
    | secret in the Lemon Squeezy dashboard under the "Webhooks" section.
    |
    */

    'signing_secret' => env('LEMON_SQUEEZY_SIGNING_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Url Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI where routes from Lemon Squeezy will be served
    | from. The URL built into Lemon Squeezy is used by default; however,
    | you can modify this path as you see fit for your application.
    |
    */

    'path' => env('LEMON_SQUEEZY_PATH', 'lemon-squeezy'),

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Store
    |--------------------------------------------------------------------------
    |
    | This is the ID of your Lemon Squeezy store. You can find your store
    | ID in the Lemon Squeezy dashboard. The entered value should be the
    | part after the # sign.
    |
    */

    'store' => env('LEMON_SQUEEZY_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Default Redirect URL
    |--------------------------------------------------------------------------
    |
    | This is the default redirect URL that will be used when a customer
    | is redirected back to your application after completing a purchase
    | from a checkout session in your Lemon Squeezy store.
    |
    */

    'redirect_url' => null,

];
