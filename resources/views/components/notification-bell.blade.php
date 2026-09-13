<div x-data="notifBell({
    indexUrl: @js(route('notifications.index')),
    readAllUrl: @js(route('notifications.readAll')),
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
            <a :href="n.url"
                class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0">
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