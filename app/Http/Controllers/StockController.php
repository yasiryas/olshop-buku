<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Show a list of all products.
     *
     * @return \Illuminate\Http\Response
     */
    /*******  084cfd07-3ae8-430c-bed2-84a17464733a  *******/
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stocks.index', ['products' => $products, 'search' => $search]);
    }

    public function show(Product $product)
    {
        return view('stocks.show', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $product->stockMutations()->create([
            'type' => $request->type,
            'quantity' => $request->quantity,
            'description' => $request->description,
        ]);

        return redirect()->route('stocks.index')->with('success', 'Stock updated successfully.');
    }

    public function history(Product $product)
    {
        $mutations = $product->stockMutations()->orderBy('created_at', 'desc')->get();
        return view('admin.stocks.history', compact('product', 'mutations'));
    }

    public function allHistory()
    {
        $mutations = \App\Models\StockMutation::with('product')->orderBy('created_at', 'desc')->get();
        return view('admin.stocks.all_history', compact('mutations'));
    }
}
