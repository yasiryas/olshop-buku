@props(['class' => 'bg-gray-100 text-gray-800'])

<span {{ $attributes->merge(['class' => 'badge-pill ' . $class]) }}>
    {{ $slot }}
</span>