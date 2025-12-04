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
    public function index()
    {
        $products = Product::all();
        return view('admin.stocks.index', compact('products'));
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
        return view('stocks.history', compact('product', 'mutations'));
    }
}
