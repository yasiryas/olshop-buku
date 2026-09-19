<div x-data="notifBell({
    indexUrl: @js(route('notifications.index')),
    readUrl: @js(route('notifications.read', ['notification' => ':id'])),
    readAllUrl: @js(route('notifications.readAll')),
    previewUrl: @js(route('product_transactions.preview', ['productTransaction' => ':id'])),
    ordersUrl: @js(route('product_transactions.index')),
    isAdmin: @js(auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false),
})" class="relative">
    <button type="button" @click="toggle"
        class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition"
        aria-label="Notifikasi">
        <i class="fas fa-bell text-lg"></i>
        <span x-show="unread > 0" x-cloak
            class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center"
            x-text="unread"></span>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak x-transition
        class="absolute right-0 top-full mt-2 w-80 max-h-[26rem] overflow-y-auto rounded-xl bg-white shadow-lg ring-1 ring-gray-200 z-50">
        <div class="sticky top-0 bg-white border-b border-gray-100 flex items-center justify-between px-4 py-3">
            <p class="font-bold text-sm">Notifikasi</p>
            <button type="button" @click="markAllRead()"
                class="text-xs font-semibold text-indigo-600 hover:underline">Tandai semua dibaca</button>
        </div>

        <template x-if="items.length === 0">
            <p class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada notifikasi.</p>
        </template>

        <template x-for="n in items" :key="n.id">
            <a :href="getNotificationUrl(n)"
                class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0"
                @click.prevent="handleNotificationClick(n)">
                <div class="flex items-start gap-2">
                    <span x-show="!n.read" class="w-2 h-2 mt-1.5 rounded-full bg-red-500 shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-sm text-gray-700" x-text="n.message"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="n.time"></p>
                    </div>
                </div>
            </a>
        </template>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notifBell', ({ indexUrl, readAllUrl, previewUrl, ordersUrl, isAdmin }) => ({
            open: false,
            unread: 0,
            items: [],

            init() {
                this.load();
                setInterval(() => this.load(), 60000);
                window.addEventListener('notification-marked-read', (e) => this.removeItem(e.detail));
            },

            async toggle() {
                this.open = !this.open;
                if (this.open) {
                    await this.load();
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
                    this.items = [];
                    this.unread = 0;
                } catch (e) {
                    console.error('Gagal menandai notifikasi dibaca:', e);
                }
            },

            async markRead(n) {
                if (n.read) return;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    await fetch(readUrl.replace(':id', n.id), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        }
                    });
                    this.removeItem(n.id);
                    await this.load();
                } catch (e) {
                    console.error('Gagal menandai notifikasi dibaca:', e);
                }
            },

            removeItem(id) {
                this.items = this.items.filter((item) => item.id !== id);
            },

            extractOrderId(n) {
                if (n.order_id) return n.order_id;
                const match = (n.url || '').match(/\/product_transactions\/(\d+)/);
                return match ? match[1] : null;
            },

            getNotificationUrl(n) {
                // For admin users with order notifications, use preview URL
                const orderId = this.extractOrderId(n);
                if (isAdmin && orderId) {
                    return previewUrl.replace(':id', orderId);
                }
                return n.url;
            },

            async handleNotificationClick(n) {
                await this.markRead(n);
                const orderId = this.extractOrderId(n);
                if (isAdmin && orderId) {
                    const url = previewUrl.replace(':id', orderId);
                    if (document.getElementById('order-detail-content')) {
                        // Dedicated event so the URL payload isn't lost (Alpine $dispatch only passes 2 args)
                        document.dispatchEvent(new CustomEvent('open-order-preview', { detail: { url } }));
                        return;
                    }
                    // Toko (halaman front) tanpa modal preview: arahkan ke daftar pesanan
                    window.location.href = ordersUrl;
                    return;
                }
                // Default: navigate normally (handled by <a href>)
                if (n.url) window.location.href = n.url;
            }
        }));
    });
</script>