@props(['href', 'dark' => false])

@php($href = $href instanceof LaravelLemonSqueezy\Checkout ? $href->url() : $href)

<a
    href="{!! $href !!}{!! str_contains($href, '?') ? '&' : '?' !!}embed=1{!! $dark ? '&dark=1' : '' !!}"
    {{ $attributes->merge(['class' => 'lemonsqueezy-button']) }}
>
    {{ $slot }}
</a>
