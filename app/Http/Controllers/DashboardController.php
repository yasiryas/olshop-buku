<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\ProductTransaction;
use App\Models\StockMutation;
use App\Models\TransactionDetail;
use App\Support\StoreSettings;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private const STATUS_CHART_COLORS = [
        ProductTransaction::STATUS_PENDING => '#f97316',
        ProductTransaction::STATUS_PROCESSING => '#3b82f6',
        ProductTransaction::STATUS_SHIPPED => '#6366f1',
        ProductTransaction::STATUS_COMPLETED => '#22c55e',
        ProductTransaction::STATUS_REJECTED => '#ef4444',
        ProductTransaction::STATUS_CANCELLED => '#9ca3af',
        ProductTransaction::STATUS_RETURNED => '#a855f7',
    ];
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('owner')) {
            return $this->ownerDashboard();
        }

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        return $this->writerDashboard($user->id);
    }

    private function ownerDashboard()
    {
        $stats = $this->transactionStats();

        $bestSellers = TransactionDetail::selectRaw('product_id, SUM(qty) as total_qty, SUM(price * qty) as total_revenue')
            ->whereHas('productTransaction', fn ($q) => $q->whereIn('status', ProductTransaction::PAID_STATUSES))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $data = $stats;
        $data['statusMetas'] = $this->statusMetas();
        $data['bestSellers'] = $bestSellers;
        $data['lowStockProducts'] = $this->lowStockProducts();
        $data['recentActivities'] = $this->recentActivities();
        $data['transactions'] = ProductTransaction::with('user')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'processing' THEN 1 ELSE 2 END, created_at DESC")
            ->take(10)
            ->get();

        return view('dashboard', $data);
    }

    private function adminDashboard()
    {
        $data = $this->transactionStats();
        $data['statusMetas'] = $this->statusMetas();
        $data['statusCounts'] = ProductTransaction::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $data['lowStockProducts'] = $this->lowStockProducts();
        $data['lowStockThreshold'] = StoreSettings::lowStockThreshold();
        $data['returnRequests'] = ProductReturn::with(['transaction.user'])
            ->where('status', ProductReturn::STATUS_REQUESTED)
            ->latest()
            ->take(5)
            ->get();
        $data['recentActivities'] = $this->recentActivities();
        $data['transactions'] = ProductTransaction::with('user')->where('status', ProductTransaction::STATUS_PENDING)->latest()->take(10)->get();

        return view('dashboard', $data);
    }

    private function writerDashboard(int $userId)
    {
        $articles = Article::with('category')->where('user_id', $userId)->latest()->take(10)->get();
        $totalArticles = Article::where('user_id', $userId)->count();
        $publishedArticles = Article::where('user_id', $userId)->where('is_published', true)->count();

        $recentActivities = collect([
            [
                'icon' => 'fa-newspaper',
                'color' => 'bg-purple-500',
                'text' => 'Artikel "' . $articles->first()?->title . '" terbit',
                'time' => $articles->first()?->created_at,
            ],
        ])->filter(fn ($a) => $a['time']);

        return view('dashboard', [
            'totalArticles' => $totalArticles,
            'publishedArticles' => $publishedArticles,
            'articles' => $articles,
            'recentActivities' => $recentActivities,
        ]);
    }

    private function statusMetas(): array
    {
        $counts = ProductTransaction::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return array_map(
            fn (string $status) => [
                ProductTransaction::STATUS_LABELS[$status],
                $counts[$status] ?? 0,
                self::STATUS_CHART_COLORS[$status],
            ],
            array_keys(ProductTransaction::STATUS_LABELS)
        );
    }

    private function transactionStats(): array
    {
        $totalRevenue = ProductTransaction::whereIn('status', ProductTransaction::PAID_STATUSES)->sum('total_amount');
        $totalOrders = ProductTransaction::count();
        $pendingOrders = ProductTransaction::where('status', ProductTransaction::STATUS_PENDING)->count();
        $completedOrders = ProductTransaction::where('status', ProductTransaction::STATUS_COMPLETED)->count();

        $monthlyData = ProductTransaction::whereIn('status', ProductTransaction::PAID_STATUSES)
            ->whereYear('created_at', date('Y'))
            ->get(['created_at', 'total_amount'])
            ->groupBy(fn ($t) => $t->created_at->format('n'))
            ->map(fn ($rows, $month) => (object) ['month' => (int) $month, 'total' => $rows->sum('total_amount')])
            ->values();

        return compact('totalRevenue', 'totalOrders', 'pendingOrders', 'completedOrders', 'monthlyData');
    }

    private function lowStockProducts(): Collection
    {
        $threshold = StoreSettings::lowStockThreshold();

        return Product::lowStock($threshold)
            ->withStock()
            ->orderByRaw('stock_in - stock_out')
            ->take(8)
            ->get();
    }

    /**
     * Build a unified recent activity feed (orders, stock mutations, articles).
     */
    private function recentActivities(): Collection
    {
        $transactions = ProductTransaction::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'icon' => 'fa-cart-plus',
                'color' => 'bg-blue-500',
                'text' => "Pesanan baru #{$t->id} dari {$t->user->name}",
                'time' => $t->created_at,
            ]);

        $stockMutations = StockMutation::with('product')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($m) => [
                'icon' => 'fa-boxes-stacked',
                'color' => 'bg-yellow-500',
                'text' => "Stok {$m->product->name} {$m->type} {$m->quantity} unit",
                'time' => $m->created_at,
            ]);

        $articles = Article::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'icon' => 'fa-newspaper',
                'color' => 'bg-purple-500',
                'text' => "Artikel \"{$a->title}\" oleh {$a->user->name}",
                'time' => $a->created_at,
            ]);

        return $transactions
            ->concat($stockMutations)
            ->concat($articles)
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }
}
