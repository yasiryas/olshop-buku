@props(['color' => 'btn-pill-primary', 'as' => 'button'])

<{{ $as }} {{ $attributes->merge(['class' => 'btn-pill ' . $color]) }}>
    {{ $slot }}
</{{ $as }}>