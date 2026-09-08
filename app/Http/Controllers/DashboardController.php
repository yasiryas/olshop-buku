<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ProductTransaction;
use App\Models\StockMutation;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Display a dashboard based on user role.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('owner|admin')) {
            $totalRevenue = ProductTransaction::where('is_paid', true)->sum('total_amount');
            $totalOrders = ProductTransaction::count();
            $pendingOrders = ProductTransaction::where('is_paid', false)->count();

            // Monthly data for chart
            $monthlyData = ProductTransaction::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get();

            $data = compact('totalRevenue', 'totalOrders', 'pendingOrders', 'monthlyData');

            $data['recentActivities'] = $this->recentActivities();

            if ($user->hasRole('admin')) {
                // Admin: Get recent transactions data
                $data['transactions'] = ProductTransaction::with('user')->latest()->take(10)->get();
            }

            return view('dashboard', $data);
        }

        // Penulis: Get their articles
        $articles = Article::with('category')->where('user_id', $user->id)->latest()->take(10)->get();
        $totalArticles = Article::where('user_id', $user->id)->count();

        $recentActivities = collect([
            [
                'icon' => 'fa-newspaper',
                'color' => 'bg-purple-500',
                'text' => 'Artikel "' . $articles->first()?->title . '" terbit',
                'time' => $articles->first()?->created_at,
            ],
        ])->filter(fn ($a) => $a['time']);

        return view('dashboard', compact('articles', 'totalArticles', 'recentActivities'));
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
