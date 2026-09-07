<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carts = Auth::user()->carts()->with(['product' => fn ($q) => $q->withStock()])->get();

        return view('front.cart', ['carts' => $carts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($product_id)
    {
        $product = Product::findOrFail($product_id);

        if ($product->stock < 1) {
            return redirect()->back()->with('error', 'Ups, Produk sudah habis!');
        }

        $user = Auth::user();
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $product_id],
            ['quantity' => 1]
        );
        if (!$cart->wasRecentlyCreated) {
            $newQty = $cart->quantity + 1;

            if ($newQty > $product->stock) {
                return back()->with('error', 'Stok tidak mencukupi!');
            }

            $cart->update(['quantity' => $newQty]);
        }

        return redirect()->route('carts.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1|max:' . $cart->product->stock,
        ]);

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'success' => true,
            'quantity' => $cart->quantity
        ]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();

        return redirect()->route('carts.index');
    }
}
