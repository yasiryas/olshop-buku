<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
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

        $paidStatuses = [ProductTransaction::STATUS_PROCESSING, ProductTransaction::STATUS_SHIPPED, ProductTransaction::STATUS_COMPLETED];

        $transactions = ProductTransaction::query()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $revenue = (clone $transactions)->whereIn('status', $paidStatuses)->sum('total_amount');
        $orderCount = (clone $transactions)->count();
        $paidOrderCount = (clone $transactions)->whereIn('status', $paidStatuses)->count();

        $dailySales = (clone $transactions)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(CASE WHEN status IN (?, ?, ?) THEN total_amount ELSE 0 END) as revenue', $paidStatuses)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $productSales = TransactionDetail::selectRaw('product_id, SUM(qty) as total_qty, SUM(price * qty) as total_revenue')
            ->whereHas('productTransaction', fn ($q) => $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->whereIn('status', $paidStatuses))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();

        $stockReport = Product::with('category')->withStock()->get()
            ->map(fn ($product) => [
                'name' => $product->name,
                'category' => $product->category?->name,
                'stock' => $product->stock,
            ])
            ->sortBy('stock')
            ->values();

        return view('admin.reports.index', compact('from', 'to', 'revenue', 'orderCount', 'paidOrderCount', 'dailySales', 'productSales', 'stockReport'));
    }
}