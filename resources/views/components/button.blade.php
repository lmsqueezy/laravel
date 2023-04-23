@props(['href'])

@php($href = $href instanceof LemonSqueezy\Laravel\Checkout ? $href->url() : $href)

<a
    href="{!! $href !!}"
    {{ $attributes->merge(['class' => 'lemonsqueezy-button']) }}
>
    {{ $slot }}
</a>
