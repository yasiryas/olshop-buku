<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\TransactionDetail;
use App\Support\StoreSettings;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    private const EXPORT_TYPES = ['sales', 'daily', 'stock'];

    public function index(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $data = $this->reportData($from, $to);

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'lowStockThreshold' => StoreSettings::lowStockThreshold(),
            ...$data,
        ]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $type = $request->query('type', 'sales');
        if (!in_array($type, self::EXPORT_TYPES, true)) {
            abort(404);
        }

        $data = $this->reportData($from, $to);
        $lowStockThreshold = StoreSettings::lowStockThreshold();
        $rows = match ($type) {
            'sales' => $data['productSales']->map(fn ($row) => [
                $row->product->name ?? 'Produk',
                $row->product->category->name ?? '-',
                (int) $row->total_qty,
                (int) $row->total_revenue,
            ]),
            'daily' => $data['dailySales']->map(fn ($row) => [
                \Carbon\Carbon::parse($row->date)->format('Y-m-d'),
                (int) $row->total_orders,
                (int) $row->revenue,
            ]),
            'stock' => $data['stockReport']->map(fn ($row) => [
                $row['name'],
                $row['category'] ?? '-',
                (int) $row['stock'],
                $row['stock'] <= $lowStockThreshold ? 'Menipis' : 'Aman',
            ]),
        };

        $columns = match ($type) {
            'sales' => ['Produk', 'Kategori', 'Qty Terjual', 'Revenue'],
            'daily' => ['Tanggal', 'Order', 'Revenue'],
            'stock' => ['Produk', 'Kategori', 'Stok', 'Status'],
        };

        return $this->downloadXlsx("laporan-{$type}-{$from}-{$to}.xlsx", $columns, $rows);
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
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $revenue = (clone $transactions)->whereIn('status', ProductTransaction::PAID_STATUSES)->sum('total_amount');
        $orderCount = (clone $transactions)->count();
        $paidOrderCount = (clone $transactions)->whereIn('status', ProductTransaction::PAID_STATUSES)->count();

        $dailySales = (clone $transactions)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(CASE WHEN status IN (?, ?, ?, ?) THEN total_amount ELSE 0 END) as revenue', ProductTransaction::PAID_STATUSES)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $productSales = TransactionDetail::selectRaw('product_id, SUM(qty) as total_qty, SUM(price * qty) as total_revenue')
            ->whereHas('productTransaction', fn ($q) => $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->whereIn('status', ProductTransaction::PAID_STATUSES))
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();

        $stockReport = Product::with('category')
            ->select(['id', 'name', 'category_id'])
            ->withStock()
            ->orderByRaw('stock_in - stock_out')
            ->get()
            ->map(fn ($product) => [
                'name' => $product->name,
                'category' => $product->category?->name,
                'stock' => $product->stock,
            ])
            ->values();

        return compact('revenue', 'orderCount', 'paidOrderCount', 'dailySales', 'productSales', 'stockReport');
    }

    private function downloadXlsx(string $filename, array $columns, Collection $rows)
    {
        $content = XlsxWriter::create('Laporan', $columns, $rows->toArray());

        return Response::make($content, 200, [
            'Content-Type' => XlsxWriter::mime(),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
