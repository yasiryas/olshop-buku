@extends('layouts.admin')

@section('content')
    <h2 class="text-xl font-bold mb-6">Riwayat Stok — {{ $product->name }}</h2>

    <table class="w-full border">
        <tr class="bg-gray-200">
            <th class="p-2">Tanggal</th>
            <th class="p-2">Tipe</th>
            <th class="p-2">Jumlah</th>
            <th class="p-2">Deskripsi</th>
        </tr>

        @foreach ($mutations as $m)
            <tr>
                <td class="border p-2">{{ $m->created_at }}</td>
                <td class="border p-2">
                    <span class="{{ $m->type == 'in' ? 'text-green-600' : 'text-red-600' }}">
                        {{ strtoupper($m->type) }}
                    </span>
                </td>
                <td class="border p-2">{{ $m->amount }}</td>
                <td class="border p-2">{{ $m->description }}</td>
            </tr>
        @endforeach
    </table>
@endsection
