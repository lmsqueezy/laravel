@props(['href', 'dark' => false])

@if ($href instanceof LemonSqueezy\Laravel\Checkout)
    @if ($dark)
        @php($href = $href->dark())
    @endif

    @php($href = $href->embed()->url())
@endif

<a
    href="{!! $href !!}"
    {{ $attributes->merge(['class' => 'lemonsqueezy-button']) }}
>
    {{ $slot }}
</a>
