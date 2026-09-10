<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\StockMutation;
use App\Models\TransactionDetail;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private const PAID_STATUSES = [
        ProductTransaction::STATUS_PROCESSING,
        ProductTransaction::STATUS_SHIPPED,
        ProductTransaction::STATUS_COMPLETED,
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
            ->whereHas('productTransaction', fn ($q) => $q->whereIn('status', self::PAID_STATUSES))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $data = $stats;
        $data['bestSellers'] = $bestSellers;
        $data['lowStockProducts'] = $this->lowStockProducts();
        $data['recentActivities'] = $this->recentActivities();
        $data['transactions'] = ProductTransaction::with('user')->latest()->take(10)->get();

        return view('dashboard', $data);
    }

    private function adminDashboard()
    {
        $data = $this->transactionStats();
        $data['statusCounts'] = ProductTransaction::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $data['lowStockProducts'] = $this->lowStockProducts();
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

    private function transactionStats(): array
    {
        $totalRevenue = ProductTransaction::whereIn('status', self::PAID_STATUSES)->sum('total_amount');
        $totalOrders = ProductTransaction::count();
        $pendingOrders = ProductTransaction::where('status', ProductTransaction::STATUS_PENDING)->count();
        $completedOrders = ProductTransaction::where('status', ProductTransaction::STATUS_COMPLETED)->count();

        $monthlyData = ProductTransaction::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereIn('status', self::PAID_STATUSES)
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        return compact('totalRevenue', 'totalOrders', 'pendingOrders', 'completedOrders', 'monthlyData');
    }

    private function lowStockProducts(): Collection
    {
        $threshold = 5;

        return Product::withStock()->get()
            ->filter(fn ($product) => $product->stock <= $threshold)
            ->sortBy('stock')
            ->take(8)
            ->values();
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
