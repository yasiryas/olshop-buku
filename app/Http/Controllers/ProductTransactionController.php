<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProductTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = Auth::user();

        $query = ProductTransaction::query()->with('user');

        if ($user->hasAnyRole(['buyer', 'penulis'])) {
            $query->where('user_id', $user->id);
            $view = 'front.product_transaction.index';
        } elseif ($user->hasRole('admin')) {
            $view = 'admin.product_transaction.index';
        } else {
            abort(403);
        }

        // Search
        $query->when($search, function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%");
        });

        // Pagination
        $product_transactions = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view($view, compact('product_transactions', 'search'));
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
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'address' => 'required|string|max:512',
            'city' => 'required|string|max:255',
            'post_code' => 'required|integer',
            'phone_number' => 'required',
            'notes' => 'max:65535',
            'proof' => 'required|image|mimes:png,jpg,jpeg',
        ]);
        DB::beginTransaction();
        try {
            $cartItems = $user->carts()->with('product')->get();
            if ($cartItems->isEmpty()) {
                throw new \Exception('Your cart is empty');
            }

            $subTotal = 0;
            foreach ($cartItems as $item) {
                $subTotal += $item->product->price * $item->quantity;
            }

            $tax = (11 / 100) * $subTotal;
            $insurance = (23 / 100) * $subTotal;
            $grandTotal = $subTotal + $tax + $insurance;

            $validated['user_id'] = $user->id;
            $validated['total_amount'] = $grandTotal;
            $validated['is_paid'] = false;

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('payment_proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $newTransaction = ProductTransaction::create($validated);

            foreach ($cartItems as $item) {
                TransactionDetail::create([
                    'product_transaction_id' => $newTransaction->id,
                    'product_id' => $item->product_id,
                    'price' => $item->product->price,
                    'qty' => $item->quantity,
                ]);
                $item->delete();
            }
            DB::commit();
            return redirect()->route('product_transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTransaction $productTransaction)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['buyer', 'penulis']) && $productTransaction->user_id !== $user->id) {
            abort(403);
        }

        $productTransaction = ProductTransaction::with(['transactionDetails.product' => fn ($q) => $q->withStock()])->find($productTransaction->id);

        if ($user->hasRole('admin')) {
            return view('admin.product_transaction.details', ['product_transaction' => $productTransaction]);
        }

        if ($user->hasAnyRole(['buyer', 'penulis'])) {
            return view('front.product_transaction.details', ['product_transaction' => $productTransaction]);
        }

        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductTransaction $productTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $transaction = ProductTransaction::with(['transactionDetails.product' => fn ($q) => $q->withStock()])->findOrFail($id);

        if ($transaction->is_paid) {
            return redirect()->back()->with('error', 'Order sudah di-approve, stok tidak boleh dikurangi dua kali!');
        }

        DB::beginTransaction();

        try {
            foreach ($transaction->transactionDetails as $detail) {
                if ($detail->product->stock < $detail->qty) {
                    throw new \Exception("Stok produk {$detail->product->name} tidak mencukupi!");
                }
            }

            foreach ($transaction->transactionDetails as $detail) {
                $detail->product->stockMutations()->create([
                    'type'        => 'out',
                    'quantity'    => $detail->qty,
                    'description' => 'Stock keluar untuk order #' . $transaction->id,
                ]);
            }

            $transaction->update(['is_paid' => true]);

            DB::commit();

            return redirect()
                ->route('product_transactions.show', $transaction->id)
                ->with('success', 'Order berhasil di-approve & stok berhasil dikurangi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductTransaction $productTransaction)
    {
        //
    }
}
