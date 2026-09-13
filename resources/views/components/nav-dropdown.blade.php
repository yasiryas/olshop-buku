@props(['label', 'align' => 'left', 'width' => '48', 'active' => false])

@php
$triggerClasses = ($active ?? false)
    ? 'inline-flex items-center px-1 pt-1 h-full border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
    : 'inline-flex items-center px-1 pt-1 h-full border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';

$panelWidth = $width === '48' ? 'w-48' : $width;

$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'right' => 'ltr:origin-top-right rtl:origin-top-left end-0',
    default => 'origin-top',
};
@endphp

<div class="relative" x-data="dropdownMenu" @click.outside="open = false" @close.stop="open = false"
        @mouseenter="openOnHover()" @mouseleave="closeOnLeave()">
    <button type="button" class="{{ $triggerClasses }}" @click="toggle()">
        {{ $label }}
        <svg class="ms-1 h-4 w-4 fill-current transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $panelWidth }} rounded-lg shadow-lg {{ $alignmentClasses }}"
        style="display: none;"
        @click="open = false">
        <div class="rounded-lg ring-1 ring-gray-200 overflow-hidden py-1 bg-white">
            {{ $slot }}
        </div>
    </div>
</div>