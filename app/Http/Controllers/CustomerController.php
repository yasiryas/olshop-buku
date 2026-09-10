<?php

namespace App\Http\Controllers;

use App\Models\ProductTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::whereHas('roles', fn ($q) => $q->where('name', 'buyer'))
            ->withCount([
                'productTransactions as total_orders',
                'productTransactions as total_spent' => fn ($q) => $q->selectRaw('COALESCE(SUM(total_amount), 0)')->whereIn('status', [
                    ProductTransaction::STATUS_PROCESSING,
                    ProductTransaction::STATUS_SHIPPED,
                    ProductTransaction::STATUS_COMPLETED,
                ]),
            ])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        abort_unless($user->hasRole('buyer'), 404);

        $transactions = $user->productTransactions()
            ->with('transactionDetails')
            ->latest()
            ->paginate(10);

        return view('admin.customers.show', compact('user', 'transactions'));
    }
}