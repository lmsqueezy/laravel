# Upgrade Guide

Future upgrade notes will be placed here.

## Upgrading To v1.3 From 1.x

### New Order Model

Lemon Squeezy for Laravel v1.3 adds a new `Order` model. In order for your webhooks to start filling these out, you'll need to run the relevant migration:

```shell
php artisan migrate
```

And now your webhooks will start saving newly made orders. If you're overwriting your migrations, you'll need to create [this migration](./database/migrations/2023_01_16_000003_create_orders_table.php) manually.

Previously made orders unfortunately need to be stored manually but we're planning on making a sync command in the future to make this more easily.
