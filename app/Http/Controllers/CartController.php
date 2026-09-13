<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Support\AgenWebShipping;
use App\Support\StoreSettings;
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

        $user = Auth::user();

        return view(
            'front.cart',
            [
                'carts' => $carts,
                'user' => $user,
                'userAddresses' => $user->addresses()->get(),
                'shippingRates' => StoreSettings::shippingRates(),
                'shippingZones' => StoreSettings::shippingZones(),
                'paymentMethods' => StoreSettings::paymentMethods(),
                'agenWebConfigured' => AgenWebShipping::configured(),
                'agenWebCities' => StoreSettings::agenWebCityList(),
            ]
        );
    }

    /**
     * Tarif ongkir real-time via AgenWebsite untuk kota tujuan.
     * Kode pos presisi (post_code) sudah cukup; city_id opsional.
     */
    public function rates(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'nullable|integer',
            'post_code' => 'sometimes|required|string|max:10',
        ]);

        $cartItems = Auth::user()->carts()->with('product')->get();

        return response()->json(AgenWebShipping::rates(
            (int) ($validated['city_id'] ?? 0),
            AgenWebShipping::cartWeightGrams($cartItems),
            $validated['post_code'] ?? ''
        ));
    }

    /**
     * Saran kecamatan untuk autocomplete checkout dari AgenWebsite.
     */
    public function locations(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|max:40',
        ]);

        $cities = collect(StoreSettings::agenWebCityList());

        $suggestions = collect(AgenWebShipping::districtSuggestions($validated['q']))
            ->map(function (array $row) use ($cities) {
                $match = $cities->firstWhere(
                    fn (array $city) => strtolower(trim($city['city_name'])) === strtolower(trim($row['city']))
                );

                $row['city_id'] = $match['city_id'] ?? null;

                return $row;
            })
            ->values();

        return response()->json($suggestions);
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
        $product = Product::withStock()->findOrFail($product_id);

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
        abort_unless($cart->user_id === Auth::id(), 403, 'Anda tidak dapat mengubah keranjang pengguna lain.');

        $cart->load(['product' => fn ($q) => $q->withStock()]);

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
        abort_unless($cart->user_id === Auth::id(), 403, 'Anda tidak dapat mengubah keranjang pengguna lain.');

        $cart->delete();

        return redirect()->route('carts.index');
    }
}
