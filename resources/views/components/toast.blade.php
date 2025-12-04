<div x-data="{
    show: false,
    message: '',
    type: 'success'
}" x-init="@if (session('success')) type = 'success';
            message = '{{ session('success') }}';
            show = true;
            setTimeout(() => show = false, 3000); @endif

@if (session('error')) type = 'error';
            message = '{{ session('error') }}';
            show = true;
            setTimeout(() => show = false, 3000); @endif" x-show="show" x-transition
    class="fixed top-5 right-5 z-[999] px-4 py-3 rounded-lg shadow-lg overflow-hidden"
    :class="{
        'bg-green-600 text-white': type === 'success',
        'bg-red-600 text-white': type === 'error'
    }">
    <span x-text="message"></span>

    <!-- Progress Bar -->
    <div class="absolute bottom-0 left-0 h-[3px] bg-white opacity-60" x-show="show" x-transition style="width: 100%;"
        x-init="$nextTick(() => {
            $el.style.transition = 'width 3s linear';
            $el.style.width = '0%';
        });"></div>
</div>
