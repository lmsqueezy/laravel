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

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Products
    |--------------------------------------------------------------------------
    |
    | This is an array of products that you have created in your Lemon Squeezy
    | store. Each product should have a unique ID, name, and variants. The
    | variants should have a unique ID and name.
    |
    | Note to developers: this config setting is not yet used by the package.
    |
    */

    'products' => [

        // 'tailwind_ui' => [
        //     'id' => 'a41526f2-6a0b-47df-aaf3-8ab6e0e88976',
        //     'name' => 'Tailwind UI',
        //     'variants' => [
        //         'default' => [
        //             'id' => '149950',
        //         ],
        //     ],
        // ],

        // 'forge_basic' => [
        //     'id' => '7708360f-f2ec-425b-abce-0162f097e5e3',
        //     'name' => 'Forge Basic',
        //     'variants' => [
        //         'monthly' => [
        //             'id' => '249950',
        //             'name' => 'Monthly',
        //         ],
        //         'yearly' => [
        //             'id' => '249951',
        //             'name' => 'Yearly',
        //         ],
        //     ],
        // ],

        // 'forge_business' => [
        //     'id' => 'cf6aab9a-5c59-49f0-be43-f3cb044d2c50',
        //     'name' => 'Forge Basic',
        //     'variants' => [
        //         'monthly' => [
        //             'id' => '349950',
        //             'name' => 'Monthly',
        //         ],
        //         'yearly' => [
        //             'id' => '349951',
        //             'name' => 'Yearly',
        //         ],
        //     ],
        // ],

    ],

];
