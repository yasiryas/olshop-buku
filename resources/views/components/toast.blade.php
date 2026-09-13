@php
    $flashMessage = session('success') ?? session('error') ?? null;
    $flashType = session('success') ? 'success' : (session('error') ? 'error' : '');
    $toastMessage = $errors->any() ? $errors->first() : $flashMessage;
    $toastType = $errors->any() ? 'error' : $flashType;
    $waLink = session('wa_link');
    $toastData = [
        'message' => $toastMessage,
        'type' => $toastType ?: 'success',
        'link' => $waLink,
    ];
@endphp

<script type="application/json" id="toast-data">@json($toastData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>

<div x-data="{
    show: false,
    message: '',
    type: 'success',
    link: '',
    fire(message, type = 'success', link = '') {
        this.message = message;
        this.type = type;
        this.link = link;
        this.show = true;
        this.$nextTick(() => {
            if (!this.$refs.progress) return;
            requestAnimationFrame(() => {
                const bar = this.$refs.progress;
                bar.style.transition = 'none';
                bar.style.width = '100%';
                requestAnimationFrame(() => {
                    bar.style.transition = 'width 3.5s linear';
                    bar.style.width = '0%';
                });
            });
        });
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this.show = false, 3500);
    },
    get wrapClass() {
        return {
            success: 'bg-emerald-600 text-white',
            error: 'bg-red-600 text-white',
            warning: 'bg-amber-500 text-white',
        }[this.type] || 'bg-gray-800 text-white';
    },
    get icon() {
        return {
            success: 'fa-circle-check',
            error: 'fa-circle-xmark',
            warning: 'fa-triangle-exclamation',
        }[this.type] || 'fa-circle-info';
    }
}"
    x-init="
        const data = JSON.parse(document.getElementById('toast-data').textContent);
        if (data.message) fire(data.message, data.type, data.link);
    "
    @show-toast.window="fire($event.detail.message, $event.detail.type, $event.detail.link)"
    x-show="show"
    x-cloak
    x-transition.opacity.duration.300ms
    :class="wrapClass"
    class="fixed top-5 right-5 z-[999] w-[calc(100vw-2.5rem)] max-w-sm flex items-start gap-3 px-4 py-3 rounded-lg shadow-lg overflow-hidden"
    role="status">
    <i class="fas mt-0.5" :class="icon"></i>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold leading-snug break-words" x-text="message"></p>
        <a x-show="link" :href="link" target="_blank" x-cloak
            class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide px-3 py-1.5 rounded-full bg-white/25 hover:bg-white/40 transition">
            <i class="fab fa-whatsapp"></i> Kirim WA
        </a>
    </div>
    <button type="button" @click="show = false" aria-label="Tutup" class="shrink-0 opacity-70 hover:opacity-100 transition">
        <i class="fas fa-xmark"></i>
    </button>
    <div x-ref="progress" class="absolute bottom-0 left-0 h-[3px] bg-white opacity-70"></div>
</div>