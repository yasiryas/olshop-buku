<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laporan') }}
            </h2>
            <form action="{{ route('admin.reports.index') }}"
                x-data="searchableList('{{ route('admin.reports.index') }}', 'results-reports')"
                @submit.prevent="search()"
                class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-600">Dari</label>
                    <input type="date" name="from" x-model="from" value="{{ $from }}"
                        @change="search()"
                        class="border-2 border-gray-300 rounded-full px-4 py-2 text-sm text-gray-700 bg-white">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-600">Sampai</label>
                    <input type="date" name="to" x-model="to" value="{{ $to }}"
                        @change="search()"
                        class="border-2 border-gray-300 rounded-full px-4 py-2 text-sm text-gray-700 bg-white">
                </div>
                <span x-show="loading" x-cloak
                    class="text-sm text-gray-500"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat...</span>
                <a :href="urlWithParams('{{ route('admin.reports.export') }}', { from: from, to: to })"
                    class="bg-green-600 text-white text-sm font-semibold py-2 px-4 rounded-full hover:bg-green-700">
                    <i class="fas fa-file-excel mr-1"></i> Export XLSX (4 Sheet)
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="results-reports">
                @include('admin.partials.report_results')
            </div>
        </div>
    </div>
</x-app-layout>