import './bootstrap';
import 'flowbite';
import Alpine from 'alpinejs';
import $ from 'jquery';
import 'select2/dist/css/select2.min.css';
import select2 from 'select2';
import 'quill/dist/quill.snow.css';
import Quill from 'quill';

window.Alpine = Alpine;
window.jQuery = window.$ = $;
select2(window, $);

Alpine.directive('select2', (el, { expression }, { Alpine: alpine }) => {
    const cfg = Object.assign(
        { width: '100%', allowClear: true },
        expression ? (() => { try { return JSON.parse(expression); } catch (_) { return {}; } })() : {}
    );

    const init = () => {
        if (!$(el).data('select2')) {
            $(el)
                .on('change', () => {
                    if (el.__s2Forwarding) return;
                    el.__s2Forwarding = true;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    setTimeout(() => { el.__s2Forwarding = false; }, 0);
                })
                .select2(cfg);
        }
    };

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(init, 0);
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }

    window.addEventListener('rajaongkir:cities-updated', () => {
        setTimeout(() => {
            if ($(el).data('select2')) $(el).trigger('change.select2');
            else init();
        }, 0);
    });
});

Alpine.data('richEditor', (textareaId) => ({
    editor: null,

    init() {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;

        this.editor = new Quill(this.$el, {
            theme: 'snow',
            placeholder: 'Tulis konten artikel di sini...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link', 'blockquote', 'code-block'],
                    ['clean'],
                ],
            },
        });

        this.editor.root.innerHTML = textarea.value;

        const sync = () => {
            textarea.value = this.editor.root.innerHTML;
        };
        this.editor.on('text-change', sync);

        const form = textarea.closest('form');
        if (form) form.addEventListener('submit', sync);
    },
}));

Alpine.data('searchableList', (baseUrl, resultsId) => ({
    keyword: '',
    from: '',
    to: '',
    status: '',
    loading: false,

    init() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) this.keyword = urlParams.get('search');
        if (urlParams.has('from')) this.from = urlParams.get('from');
        if (urlParams.has('to')) this.to = urlParams.get('to');
        if (urlParams.has('status')) this.status = urlParams.get('status');
    },

    apiUrl() {
        const url = new URL(baseUrl, window.location.origin);
        if (this.keyword.trim()) {
            url.searchParams.set('search', this.keyword.trim());
        }
        if (this.from) {
            url.searchParams.set('from', this.from);
        }
        if (this.to) {
            url.searchParams.set('to', this.to);
        }
        if (this.status) {
            url.searchParams.set('status', this.status);
        }
        return url;
    },

    resetFilters() {
        this.keyword = '';
        this.from = '';
        this.to = '';
        this.status = '';
        this.search();
    },

    urlWithParams(base, params) {
        const url = new URL(base, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            }
        });
        return url.toString();
    },

    esc(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    errorBox(message) {
        return '<div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-lg px-4 py-3 mb-6" ' +
            'role="alert"><i class="fas fa-circle-exclamation mr-2"></i>' + this.esc(message) + '</div>';
    },

    async search() {
        this.loading = true;
        const results = document.getElementById(resultsId);
        try {
            const res = await fetch(this.apiUrl(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) {
                let message = 'Gagal memuat data. Silakan coba lagi.';
                try {
                    const data = await res.json();
                    if (data.errors && Object.values(data.errors).length) {
                        const entry = Object.values(data.errors)[0];
                        message = Array.isArray(entry) ? entry[0] : entry;
                    } else if (data.message) {
                        message = data.message;
                    }
                } catch (_) {
                    // bukan JSON; pakai pesan default
                }
                results.innerHTML = this.errorBox(message);
                return;
            }

            const html = await res.text();
            results.innerHTML = html;
            Alpine.initTree(results);
        } catch (_) {
            results.innerHTML = this.errorBox('Gagal terhubung ke server. Periksa koneksi Anda.');
        } finally {
            this.loading = false;
        }
    }
}));

Alpine.data('dropdownMenu', () => ({
    open: false,

    get isTouch() {
        return window.matchMedia('(pointer: coarse)').matches;
    },

    toggle() {
        this.open = !this.open;
    },

    openOnHover() {
        if (!this.isTouch) this.open = true;
    },

    closeOnLeave() {
        if (!this.isTouch) this.open = false;
    },
}));

Alpine.data('notifBell', ({ indexUrl, readAllUrl }) => ({
    open: false,
    unread: 0,
    items: [],

    init() {
        this.load();
        setInterval(() => this.load(), 60000);
    },

    async toggle() {
        this.open = !this.open;
        if (this.open) {
            await this.load();
            if (this.unread > 0) this.markAllRead();
        }
    },

    async load() {
        try {
            const res = await fetch(indexUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.items = Array.isArray(data.data) ? data.data : [];
            this.unread = Number(data.unread || 0);
        } catch (e) {
            console.error('Gagal memuat notifikasi:', e);
        }
    },

    async markAllRead() {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            await fetch(readAllUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });
            this.unread = 0;
            this.items.forEach((n) => { n.read = true; });
        } catch (e) {
            console.error('Gagal menandai notifikasi dibaca:', e);
        }
    }
}));

Alpine.data('orderDetail', () => ({
    loading: false,

    async openDetail(url) {
        const content = document.getElementById('order-detail-content');
        if (!content) return;

        this.loading = true;
        this.$dispatch('open-modal', 'order-detail');

        const before = content.getBoundingClientRect().height;
        content.style.minHeight = Math.max(before, 120) + 'px';

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            const html = await response.text();
            content.innerHTML = html;
            content.style.minHeight = '';
            Alpine.initTree(content);
        } catch (err) {
            console.error('Gagal memuat detail pesanan:', err);
            content.innerHTML =
                '<p class="text-red-600 text-center py-8">Gagal memuat detail pesanan.</p>';
            content.style.minHeight = '';
        } finally {
            this.loading = false;
        }
    }
}));

const KEEP_ALIVE_INTERVAL = 5 * 60 * 1000;

function keepSessionAlive() {
    fetch('/session/keep-alive', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        keepalive: true,
    }).catch(() => {});
}

setInterval(keepSessionAlive, KEEP_ALIVE_INTERVAL);

Alpine.start();
