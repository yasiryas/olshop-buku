<?php

namespace App\Http\Controllers;

use App\Models\ProductTransaction;
use App\Models\StockMutation;
use App\Models\TransactionDetail;
use App\Support\XlsxWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $data = $this->reportData($from, $to);

        $viewData = [
            'from' => $from,
            'to' => $to,
            ...$data,
        ];

        if ($request->ajax()) {
            return view('admin.partials.report_results', $viewData);
        }

        return view('admin.reports.index', $viewData);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $data = $this->reportData($from, $to);
        $period = Carbon::parse($from)->format('d M Y').' s/d '.Carbon::parse($to)->format('d M Y');
        $average = $data['paidOrderCount'] > 0 ? round($data['revenue'] / $data['paidOrderCount']) : 0;

        $productRows = $data['productSales']
            ->map(fn ($row) => [
                $row->product->name ?? 'Produk',
                $row->product->category->name ?? '-',
                (int) $row->product->price,
                (int) $row->total_qty,
                (int) $row->total_revenue,
            ])
            ->push([
                'TOTAL',
                '',
                '',
                (int) $data['productSales']->sum('total_qty'),
                (int) $data['productSales']->sum('total_revenue'),
            ])
            ->toArray();

        $dailyRows = $data['dailySales']
            ->map(fn ($row) => [
                Carbon::parse($row->date)->format('d M Y'),
                (int) $row->total_orders,
                (int) $row->paid_orders,
                (int) $row->revenue,
            ])
            ->push([
                'TOTAL',
                (int) $data['dailySales']->sum('total_orders'),
                (int) $data['dailySales']->sum('paid_orders'),
                (int) $data['dailySales']->sum('revenue'),
            ])
            ->toArray();

        $mutationRows = $data['mutationReport']
            ->map(fn ($row) => [
                $row['name'],
                $row['category'] ?? '-',
                (int) $row['in'],
                (int) $row['out'],
                (int) $row['net'],
            ])
            ->push([
                'TOTAL',
                '',
                (int) $data['mutationReport']->sum('in'),
                (int) $data['mutationReport']->sum('out'),
                (int) $data['mutationReport']->sum('net'),
            ])
            ->toArray();

        $summaryRows = [
            ['Periode', $period],
            ['Pendapatan', rupiah($data['revenue'])],
            ['Total Pesanan', (string) $data['orderCount']],
            ['Pesanan Terbayar', (string) $data['paidOrderCount']],
            ['Rata-rata / Pesanan', rupiah($average)],
            ['Produk Terjual', number_format((int) $data['productSales']->sum('total_qty'), 0, ',', '.').' Pcs'],
            ['Hari Transaksi', (string) $data['dailySales']->count().' hari'],
        ];

        $sheets = [
            [
                'name' => 'Ringkasan',
                'title' => 'Ringkasan Laporan Wigati Buku',
                'columns' => ['Keterangan', 'Nilai'],
                'rows' => $summaryRows,
            ],
            [
                'name' => 'Penjualan per Produk',
                'title' => "Laporan Wigati Buku - Penjualan per Produk ($period)",
                'columns' => ['Produk', 'Kategori', 'Harga Satuan', 'Qty Terjual', 'Revenue'],
                'rows' => $productRows,
            ],
            [
                'name' => 'Penjualan Harian',
                'title' => "Laporan Wigati Buku - Penjualan Harian ($period)",
                'columns' => ['Tanggal', 'Order', 'Order Dibayar', 'Revenue'],
                'rows' => $dailyRows,
            ],
            [
                'name' => 'Riwayat Mutasi Stok',
                'title' => "Laporan Wigati Buku - Riwayat Mutasi Stok ($period)",
                'columns' => ['Produk', 'Kategori', 'Masuk', 'Keluar', 'Bersih'],
                'rows' => $mutationRows,
            ],
        ];

        return $this->downloadXlsx("Laporan-Wigati-Buku_{$from}_sd_{$to}.xlsx", $sheets);
    }

    private function resolveRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
        ], [
            'from.before_or_equal' => 'Tanggal "Dari" tidak boleh setelah tanggal "Sampai".',
            'to.after_or_equal' => 'Tanggal "Sampai" tidak boleh sebelum tanggal "Dari".',
        ]);

        $from = ($validated['from'] ?? null) ?: now()->startOfMonth()->toDateString();
        $to = ($validated['to'] ?? null) ?: now()->toDateString();

        return [$from, $to];
    }

    private function reportData(string $from, string $to): array
    {
        $transactions = ProductTransaction::query()
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        $revenue = (clone $transactions)->whereIn('status', ProductTransaction::PAID_STATUSES)->sum('total_amount');
        $orderCount = (clone $transactions)->count();
        $paidOrderCount = (clone $transactions)->whereIn('status', ProductTransaction::PAID_STATUSES)->count();

        $dailySales = (clone $transactions)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, '
                .'SUM(CASE WHEN status IN (?, ?, ?, ?) THEN 1 ELSE 0 END) as paid_orders, '
                .'SUM(CASE WHEN status IN (?, ?, ?, ?) THEN total_amount ELSE 0 END) as revenue',
                [...ProductTransaction::PAID_STATUSES, ...ProductTransaction::PAID_STATUSES])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $productSales = TransactionDetail::selectRaw('product_id, SUM(qty) as total_qty, SUM(price * qty) as total_revenue')
            ->whereHas('productTransaction', fn ($q) => $q->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->whereIn('status', ProductTransaction::PAID_STATUSES))
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();

        $mutationReport = StockMutation::with('product.category')
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->get()
            ->groupBy('product_id')
            ->map(function ($rows) {
                $product = $rows->first()->product;
                $stockIn = (int) $rows->where('type', 'in')->sum('quantity');
                $stockOut = (int) $rows->where('type', 'out')->sum('quantity');

                return [
                    'name' => $product->name ?? 'Produk',
                    'category' => $product->category?->name,
                    'in' => $stockIn,
                    'out' => $stockOut,
                    'net' => $stockIn - $stockOut,
                ];
            })
            ->values();

        return compact('revenue', 'orderCount', 'paidOrderCount', 'dailySales', 'productSales', 'mutationReport');
    }

    private function downloadXlsx(string $filename, array $sheets)
    {
        $content = XlsxWriter::createMulti($sheets);

        return Response::make($content, 200, [
            'Content-Type' => XlsxWriter::mime(),
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
