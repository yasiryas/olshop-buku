<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\TransactionDetail;
use App\Models\ProductTransaction;
use App\Support\StoreSettings;
use App\Support\WaNotifier;
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

        if ($user->hasRole('buyer')) {
            $query->where('user_id', $user->id);
            $view = 'front.product_transaction.index';
        } elseif ($user->hasAnyRole(['owner', 'admin'])) {
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

        if ($request->ajax()) {
            if ($user->hasRole('buyer')) {
                return view('front.partials.orders_list', compact('product_transactions', 'search'));
            }
            return view('admin.partials.orders_list', compact('product_transactions', 'search'));
        }

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

        $zoneCities = StoreSettings::shippingZoneCities();
        if (empty($zoneCities)) {
            throw ValidationException::withMessages([
                'city' => ['Belum ada zona pengiriman. Hubungi admin.'],
            ]);
        }

        $shippingRates = StoreSettings::shippingRatesFor($request->input('city', ''));
        $paymentMethods = StoreSettings::paymentMethods();

        $validated = $request->validate([
            'address' => 'required|string|max:512',
            'city' => 'required|in:' . implode(',', $zoneCities),
            'post_code' => 'required|integer',
            'phone_number' => 'required',
            'notes' => 'max:65535',
            'proof' => 'required|image|mimes:png,jpg,jpeg',
            'shipping_method' => 'required|in:' . implode(',', array_column($shippingRates, 'code')),
            'payment_method' => 'required|in:' . implode(',', array_column($paymentMethods, 'code')),
        ]);
        DB::beginTransaction();
        try {
            $cartItems = $user->carts()->with('product')->get();
            if ($cartItems->isEmpty()) {
                throw new \Exception('Your cart is empty');
            }

            foreach ($cartItems as $item) {
                $product = Product::withStock()->find($item->product_id);
                if (!$product || $product->stock < $item->quantity) {
                    $prodName = $product ? $product->name : 'Produk';
                    $prodStock = $product ? $product->stock : 0;
                    throw new \Exception("Stok produk '{$prodName}' tidak mencukupi! Tersisa: {$prodStock}");
                }
            }

            $subTotal = 0;
            foreach ($cartItems as $item) {
                $subTotal += $item->product->price * $item->quantity;
            }

            $tax = (11 / 100) * $subTotal;
            $insurance = (23 / 100) * $subTotal;

            $selectedShipping = collect($shippingRates)->firstWhere('code', $request->shipping_method);
            $selectedPayment = collect($paymentMethods)->firstWhere('code', $request->payment_method);

            $shippingCost = (int) ($selectedShipping['cost'] ?? 0);
            $grandTotal = $subTotal + $tax + $insurance + $shippingCost;

            $validated['user_id'] = $user->id;
            $validated['total_amount'] = $grandTotal;
            $validated['is_paid'] = false;
            $validated['status'] = ProductTransaction::STATUS_PENDING;
            $validated['shipping_method'] = $selectedShipping['courier'] ?? $request->shipping_method;
            $validated['shipping_cost'] = $shippingCost;
            $validated['payment_method'] = $selectedPayment['name'] ?? $request->payment_method;

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

        if ($user->hasAnyRole(['buyer'])) {
            if ($productTransaction->user_id !== $user->id) {
                abort(403);
            }
        }

        $productTransaction = ProductTransaction::with([
            'returns',
            'transactionDetails.product' => fn ($q) => $q->withStock(),
        ])->find($productTransaction->id);

        if ($user->hasAnyRole(['owner', 'admin'])) {
            return view('admin.product_transaction.details', ['product_transaction' => $productTransaction]);
        }

        if ($user->hasRole('buyer')) {
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
     * Approve: pending -> processing (verifikasi pembayaran), stok keluar.
     */
    public function approve(ProductTransaction $productTransaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403);

        if ($productTransaction->status !== ProductTransaction::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Hanya pesanan pending yang bisa di-approve.');
        }

        $transaction = ProductTransaction::with(['transactionDetails.product' => fn ($q) => $q->withStock()])->findOrFail($productTransaction->id);

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

            $transaction->update([
                'is_paid' => true,
                'status'  => ProductTransaction::STATUS_PROCESSING,
            ]);

            DB::commit();

            $waMessage = "Halo {$transaction->user->name}, pesanan #{$transaction->id} Anda telah kami terima dan sedang diproses. Terima kasih sudah berbelanja di Wigati Buku.";

            return redirect()
                ->route('product_transactions.show', $transaction->id)
                ->with('success', 'Order di-approve & stok berhasil dikurangi.')
                ->with('wa_link', WaNotifier::send($transaction->phone_number, $waMessage));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ship: processing -> shipped, input nomor resi.
     */
    public function ship(Request $request, ProductTransaction $productTransaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403);

        if ($productTransaction->status !== ProductTransaction::STATUS_PROCESSING) {
            return redirect()->back()->with('error', 'Hanya pesanan berstatus diproses yang bisa dikirim.');
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        $productTransaction->update([
            'status' => ProductTransaction::STATUS_SHIPPED,
            'tracking_number' => $validated['tracking_number'],
        ]);

        $waMessage = "Halo {$productTransaction->user->name}, pesanan #{$productTransaction->id} sudah dikirim via {$productTransaction->shipping_method}. Nomor resi: {$validated['tracking_number']}. Terima kasih!";

        return redirect()
            ->route('product_transactions.show', $productTransaction->id)
            ->with('success', 'Order ditandai terkirim.')
            ->with('wa_link', WaNotifier::send($productTransaction->phone_number, $waMessage));
    }

    /**
     * Complete: shipped -> completed.
     */
    public function complete(ProductTransaction $productTransaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403);

        if ($productTransaction->status !== ProductTransaction::STATUS_SHIPPED) {
            return redirect()->back()->with('error', 'Pesanan harus berstatus dikirim sebelum diselesaikan.');
        }

        $productTransaction->update([
            'status' => ProductTransaction::STATUS_COMPLETED,
        ]);

        $waMessage = "Halo {$productTransaction->user->name}, pesanan #{$productTransaction->id} telah selesai. Terima kasih sudah berbelanja di Wigati Buku.";

        return redirect()
            ->route('product_transactions.show', $productTransaction->id)
            ->with('success', 'Order selesai.')
            ->with('wa_link', WaNotifier::send($productTransaction->phone_number, $waMessage));
    }

    /**
     * Reject: pending -> rejected.
     */
    public function reject(Request $request, ProductTransaction $productTransaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403);

        if ($productTransaction->status !== ProductTransaction::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Hanya pesanan pending yang bisa ditolak.');
        }

        $validated = $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        $productTransaction->update([
            'status' => ProductTransaction::STATUS_REJECTED,
            'rejection_note' => $validated['rejection_note'],
        ]);

        $waMessage = "Halo {$productTransaction->user->name}, mohon maaf pesanan #{$productTransaction->id} terpaksa kami tolak. Alasan: {$validated['rejection_note']}.";

        return redirect()
            ->route('product_transactions.show', $productTransaction->id)
            ->with('success', 'Order ditolak.')
            ->with('wa_link', WaNotifier::send($productTransaction->phone_number, $waMessage));
    }

    /**
     * Cancel order (keep the record for history/audit).
     */
    public function destroy(ProductTransaction $productTransaction)
    {
        $user = auth()->user();

        if ($user->hasRole('buyer')) {
            abort_unless($productTransaction->user_id === $user->id, 403);
            abort_unless($productTransaction->status === ProductTransaction::STATUS_PENDING, 403);
        } elseif (!$user->hasAnyRole(['owner', 'admin'])) {
            abort(403);
        }

        $productTransaction->update([
            'status' => ProductTransaction::STATUS_CANCELLED,
        ]);

        return redirect()->route('product_transactions.index')->with('success', 'Order berhasil dibatalkan.');
    }
}
