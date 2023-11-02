# Release Notes

## [Unreleased](https://github.com/lmsqueezy/laravel/compare/1.2.4...main)

## [1.2.4](https://github.com/lmsqueezy/laravel/compare/1.2.3...1.2.4) - 2023-11-02

- Fix missing lemon_squeezy_id if billable was on generic trial by [@pdziewa](https://github.com/pdziewa) in https://github.com/lmsqueezy/laravel/pull/52

## [1.2.3](https://github.com/lmsqueezy/laravel/compare/1.2.2...1.2.3) - 2023-10-10

- Fix nullable lemon_squeezy_id on customers table by [@driesvints](https://github.com/driesvints) in https://github.com/lmsqueezy/laravel/pull/50

## [1.2.2](https://github.com/lmsqueezy/laravel/compare/1.2.1...1.2.2) - 2023-10-10

- PHP 8.3 support by [@driesvints](https://github.com/driesvints) in https://github.com/lmsqueezy/laravel/pull/48

## [1.2.1](https://github.com/lmsqueezy/laravel/compare/1.2.0...1.2.1) - 2023-10-05

- Fix issues with customer record by [@driesvints](https://github.com/driesvints) in https://github.com/lmsqueezy/laravel/pull/47

## [1.2.0](https://github.com/lmsqueezy/laravel/compare/1.1.2...1.2.0) - 2023-10-04

- Support for customer portals by [@driesvints](https://github.com/driesvints) in https://github.com/lmsqueezy/laravel/pull/46
- Use `hash_equals` when validating webhook signature by [@thinkverse](https://github.com/thinkverse) in https://github.com/lmsqueezy/laravel/pull/42

## [1.1.2](https://github.com/lmsqueezy/laravel/compare/1.1.1...1.1.2) - 2023-08-10

- Adding Event and Webhook handler to get license updates by [@abishekrsrikaanth](https://github.com/abishekrsrikaanth) in https://github.com/lmsqueezy/laravel/pull/41

## [1.1.1](https://github.com/lmsqueezy/laravel/compare/1.1.0...1.1.1) - 2023-08-06

- Dispatching the correct event name when a license is created by [@abishekrsrikaanth](https://github.com/abishekrsrikaanth) in https://github.com/lmsqueezy/laravel/pull/35

## [1.1.0](https://github.com/lmsqueezy/laravel/compare/1.0.1...1.1.0) - 2023-07-18

- Add support for custom name, description and receipt_thank_you_note at checkout by [@UrosCodes](https://github.com/UrosCodes) in https://github.com/lmsqueezy/laravel/pull/34

## [1.0.1](https://github.com/lmsqueezy/laravel/compare/1.0.0...1.0.1) - 2023-07-15

- Fix can't find Subscription from SubscriptionPayment by [@ycs77](https://github.com/ycs77) in https://github.com/lmsqueezy/laravel/pull/33

## [1.0.0](https://github.com/lmsqueezy/laravel/compare/0.2.1...1.0.0) - 2023-06-16

- First stable release
- Add support for custom price at checkout by @mraheelkhan in https://github.com/lmsqueezy/laravel/pull/28
- Add remaining events by @driesvints in https://github.com/lmsqueezy/laravel/pull/30

## [0.2.1](https://github.com/lmsqueezy/laravel/compare/0.2.0...0.2.1) - 2023-05-20

- Add "down" method for migrations by @Flatroy in https://github.com/lmsqueezy/laravel/pull/19
- Add endTrial method and document billing anchor by @driesvints in https://github.com/lmsqueezy/laravel/commit/2d63056363fd345ef8971d2d1651f628ef7599f1

## [0.2.0](https://github.com/lmsqueezy/laravel/compare/0.1.0...0.2.0) - 2023-05-08

- Add missing `hasExpiredTrial` method on `Subscription` model by @driesvints in https://github.com/lmsqueezy/laravel/commit/8e2e711c08245bfc0f8010531d106c9221dbab60
- Anonymous class migration by @shuvroroy in https://github.com/lmsqueezy/laravel/pull/5
- Allow regular checkouts to create subscriptions by @driesvints in https://github.com/lmsqueezy/laravel/commit/7fd4375591ac4253c61c2c1a72c0858693876a18
- Fix creation of customers by @driesvints in https://github.com/lmsqueezy/laravel/commit/29c141971d1d41c3a16b4b6aac670db45d639083

## 0.1.0 - 2023-04-28

- Initial pre-release
