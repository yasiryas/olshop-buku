<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $my_charts = $user->carts()->with('product')->get();

        return view('front.cart', ['my_carts' => $my_charts, 'carts' => $my_charts]);
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

        //cek stok
        $product = Product::find($product_id);
        if ($product->stock < 1) {
            return redirect()->back()->with('error', 'Ups, Produk sudah habis!');
        }

        $user = Auth::user();
        $existing_cart = Cart::where('user_id', $user->id)
            ->where('product_id', $product_id)
            ->first();

        if ($existing_cart) {
            return redirect()->route('carts.index');
        }

        DB::beginTransaction();
        try {
            Cart::updateOrCreate([
                'user_id' => $user->id,
                'product_id' => $product_id,
            ]);

            DB::commit();
            return redirect()->route('carts.index');
        } catch (\Exception $e) {
            DB::rollback();
            throw ValidationException::withMessages([
                'system_error' => ['System error', $e->getMessage()],
            ]);
        }
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
        //
        try {
            $cart->delete();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['system error!', $e->getMessage()]
            ]);
            throw $error;
        }
    }
}
