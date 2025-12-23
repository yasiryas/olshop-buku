<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use App\Models\ProductTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class ProductTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use HasFactory, Notifiable, HasRoles;
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = Auth::user();

        // Base query
        $query = ProductTransaction::query();

        // Role-based filtering
        if ($user->hasRole('buyer')) {
            $query->where('user_id', $user->id);
            $view = 'front.product_transaction.index';
        } elseif ($user->hasAnyRole(['admin', 'owner'])) {
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
    public function detail()
    {
        //
        if (auth()->user()->hasRole('admin|owner')) {
            return redirect()->route('admin.product_transaction.details');
        } elseif (auth()->user()->hasRole('buyer')) {
            return view('front.product_transaction.details');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $cartItems = $user->carts;
        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Ups, Balum ada produk yang anda masukan ke keranjang!');
        }

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
            $cartItems = $user->carts;
            if ($cartItems->isEmpty()) {
                throw new \Exception('Your cart is empty');
            }

            $subTotalCent = 0;
            $deliveryFeeCent = 0 * 100;

            $cartItems = $user->carts;
            foreach ($cartItems as $item) {
                $subTotalCent += ($item->product->price * $item->quantity) * 100;
            }

            $taxCent = (11 / 100) * $subTotalCent;
            $insuranceCent = (23 / 100) * $subTotalCent;
            $grandTotalCent = $subTotalCent + $deliveryFeeCent + $taxCent + $insuranceCent;

            $grandTotal = $grandTotalCent / 100;

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
        $productTransaction = ProductTransaction::with('transactionDetails.product')->find($productTransaction->id);
        if (auth()->user()->hasRole('admin|owner')) {
            return view('admin.product_transaction.details', ['product_transaction' => $productTransaction]);
        } elseif (auth()->user()->hasRole('buyer')) {
            return view('front.product_transaction.details', ['product_transaction' => $productTransaction]);
        }
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
        $transaction = ProductTransaction::with('transactionDetails.product')->findOrFail($id);

        DB::beginTransaction();

        try {

            // 1. Cek stok cukup untuk semua item
            foreach ($transaction->transactionDetails as $detail) {
                $product = $detail->product;

                $totalIn  = $product->stockMutations()->where('type', 'in')->sum('quantity');
                $totalOut = $product->stockMutations()->where('type', 'out')->sum('quantity');

                $currentStock = $totalIn - $totalOut;

                if ($currentStock < $detail->qty) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi!");
                }
            }

            // 2. Kurangi stok → tambah record di stock_mutations
            foreach ($transaction->transactionDetails as $detail) {

                $detail->product->stockMutations()->create([
                    'type'        => 'out',
                    'quantity'    => $detail->qty,
                    'description' => 'Stock keluar untuk order #' . $transaction->id,
                ]);
            }

            // 3. Set transaksi menjadi paid
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
