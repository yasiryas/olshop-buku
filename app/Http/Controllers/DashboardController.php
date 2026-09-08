<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductTransaction;
use App\Models\Article;

class DashboardController extends Controller
{
    /**
     * Display a dashboard based on user role.
     */
    public function index()
    {
        $user = auth()->user();

        // Get data based on user role
        if ($user->hasRole('owner|admin')) {
            // Admin/Owner: Get all transactions data
            $transactions = ProductTransaction::with('user')->latest()->take(10)->get();
            $totalRevenue = ProductTransaction::where('is_paid', true)->sum('total_amount');
            $totalOrders = ProductTransaction::count();
            $pendingOrders = ProductTransaction::where('is_paid', false)->count();

            // Monthly data for chart
            $monthlyData = ProductTransaction::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get();

            return view('dashboard', compact('transactions', 'totalRevenue', 'totalOrders', 'pendingOrders', 'monthlyData'));
        }

        // Penulis: Get their articles
        $articles = Article::with('category')->where('user_id', $user->id)->latest()->take(10)->get();
        $totalArticles = Article::where('user_id', $user->id)->count();

        return view('dashboard', compact('articles', 'totalArticles'));
    }
}
