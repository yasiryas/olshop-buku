@props([
    'headers' => [],
    'title' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'panel']) }}>
    @if ($title)
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="data-table">
            @if (count($headers))
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($footer)
        <div class="px-4 py-4 border-t border-gray-100">{!! $footer !!}</div>
    @endif
</div>