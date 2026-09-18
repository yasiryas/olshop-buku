<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\TransactionDetail;
use App\Models\ProductTransaction;
use App\Support\StoreSettings;
use App\Support\AgenWebShipping;
use App\Support\WaNotifier;
use App\Support\OrderNotifications;
use App\Support\AuditLogger;
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
        $from = $request->input('from');
        $to = $request->input('to');
        $status = $request->input('status');
        $validStatuses = array_keys(ProductTransaction::STATUS_LABELS);
        $user = Auth::user();

        $query = ProductTransaction::query()->with('user');

        if ($user->hasRole('buyer')) {
            $query->where('user_id', $user->id);
            $view = 'front.product_transaction.index';
        } elseif ($user->hasAnyRole(['owner', 'admin'])) {
            $view = 'admin.product_transaction.index';
        } else {
            abort(403, 'Fitur pesanan khusus untuk Owner, Admin, dan pembeli.');
        }

        // Search
        $query->when($search, function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%");
        });

        // Filter rentang tanggal
        $query->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        // Filter status
        $query->when($status && in_array($status, $validStatuses, true), fn ($q) => $q->where('status', $status));

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

    public function preview(ProductTransaction $productTransaction)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['owner', 'admin'])) {
            abort(403, 'Pratinjau pesanan khusus untuk Owner dan Admin.');
        }

        return view('admin.partials.order_preview', [
            'product_transaction' => $productTransaction->load('user', 'returns', 'transactionDetails.product'),
        ]);
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
        $agenWebConfigured = AgenWebShipping::configured();

        if (!$agenWebConfigured && empty($zoneCities)) {
            throw ValidationException::withMessages([
                'city' => ['Belum ada zona pengiriman. Hubungi admin.'],
            ]);
        }

        if ($agenWebConfigured) {
            $agenWebRates = AgenWebShipping::rates(
                (int) $request->input('agenweb_city_id', 0),
                AgenWebShipping::cartWeightGrams($user->carts()->with('product')->get()),
                (string) $request->input('post_code', '')
            );

            if (empty($agenWebRates)) {
                $agenWebConfigured = false;
            }
        } else {
            $agenWebRates = [];
        }

        $shippingRates = $agenWebConfigured
            ? $agenWebRates
            : StoreSettings::shippingRatesFor($request->input('city', ''));

        $paymentMethods = StoreSettings::paymentMethods();

        $validated = $request->validate([
            'recipient_name' => 'required|string|max:100',
            'address' => 'required|string|max:512',
            'city' => $agenWebConfigured
                ? 'required|string|max:255'
                : 'required|in:' . implode(',', $zoneCities),
            'district' => $agenWebConfigured ? 'required|string|max:150' : 'nullable|string|max:150',
            'province' => $agenWebConfigured ? 'required|string|max:100' : 'nullable|string|max:100',
            'agenweb_city_id' => 'nullable|integer',
            'post_code' => 'required|integer',
            'phone_number' => 'required|string|max:20',
            'address_label' => 'nullable|string|max:30',
            'saved_address_id' => 'nullable|integer',
            'notes' => 'max:65535',
            'proof' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'shipping_method' => $agenWebConfigured
                ? 'required|in:' . implode(',', array_column($shippingRates, 'rate_id'))
                : 'required|in:' . implode(',', array_column($shippingRates, 'code')),
            'payment_method' => 'required|in:' . implode(',', array_column($paymentMethods, 'code')),
        ]);
        DB::beginTransaction();
        try {
            $cartItems = $user->carts()->get();
            if ($cartItems->isEmpty()) {
                throw new \Exception('Keranjang belanja Anda kosong');
            }

            $products = Product::withStock()
                ->whereKey($cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subTotal = 0;
            foreach ($cartItems as $item) {
                $product = $products[$item->product_id] ?? null;
                if (!$product || $product->stock < $item->quantity) {
                    throw new \Exception("Stok produk '" . ($product->name ?? 'Produk') . "' tidak mencukupi! Tersisa: " . ($product->stock ?? 0));
                }
                $subTotal += $product->price * $item->quantity;
            }

            $taxPercent = StoreSettings::taxPercent();
            $insurancePercent = StoreSettings::insurancePercent();
            $tax = ($taxPercent / 100) * $subTotal;
            $insurance = ($insurancePercent / 100) * $subTotal;

            $selectedShipping = $agenWebConfigured
                ? collect($shippingRates)->firstWhere('rate_id', $request->shipping_method)
                : collect($shippingRates)->firstWhere('code', $request->shipping_method);

            $selectedPayment = collect($paymentMethods)->firstWhere('code', $request->payment_method);

            $shippingCost = (int) ($selectedShipping['cost'] ?? 0);
            $grandTotal = $subTotal + $tax + $insurance + $shippingCost;

            $validated['user_id'] = $user->id;
            $validated['total_amount'] = $grandTotal;
            $validated['tax_amount'] = (int) $tax;
            $validated['insurance_amount'] = (int) $insurance;
            $validated['is_paid'] = false;
            $validated['status'] = ProductTransaction::STATUS_PENDING;
            $validated['shipping_method'] = $agenWebConfigured
                ? trim(($selectedShipping['courier'] ?? '') . ' ' . ($selectedShipping['service'] ?? ''))
                : ($selectedShipping['courier'] ?? $request->shipping_method);
            $validated['shipping_cost'] = $shippingCost;
            $validated['payment_method'] = $selectedPayment['name'] ?? $request->payment_method;

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('payment_proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $newTransaction = ProductTransaction::create($validated);

            foreach ($cartItems as $item) {
                $product = $products[$item->product_id];

                TransactionDetail::create([
                    'product_transaction_id' => $newTransaction->id,
                    'product_id' => $item->product_id,
                    'price' => $product->price,
                    'qty' => $item->quantity,
                ]);

                $product->stockMutations()->create([
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'description' => 'Stok direservasi untuk order #' . $newTransaction->id,
                ]);
            }

            Cart::whereKey($cartItems->pluck('id'))->delete();

            $this->updateProfileAddress($user, $validated, $request);

            if ($request->boolean('save_address')) {
                $this->saveToAddressBook($user, $validated, $request);
            }

            DB::commit();

            OrderNotifications::orderCreated($newTransaction);
            AuditLogger::log('order.created', $newTransaction, [
                'total' => $grandTotal,
                'method' => $validated['payment_method'],
            ]);

            return redirect()->route('product_transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['Terjadi kesalahan sistem: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    private function updateProfileAddress($user, array $validated, Request $request): void
    {
        $user->update([
            'address' => $validated['address'],
            'city' => $validated['city'] ?? '',
            'post_code' => (string) $validated['post_code'],
            'phone_number' => $validated['phone_number'],
        ]);
    }

    private function saveToAddressBook($user, array $validated, Request $request): void
    {
        $existing = $request->filled('saved_address_id')
            ? $user->addresses()->find((int) $request->saved_address_id)
            : null;

        $data = [
            'label' => trim((string) $request->input('address_label')) ?: 'Alamat',
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone_number'],
            'address' => $validated['address'],
            'province' => $validated['province'] ?? '',
            'city' => $validated['city'],
            'city_id' => $request->input('agenweb_city_id'),
            'district' => $validated['district'] ?? '',
            'postal_code' => (string) $validated['post_code'],
        ];

        if ($existing) {
            $existing->update($data);

            return;
        }

        $data['is_default'] = $user->addresses()->count() === 0;

        $user->addresses()->create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTransaction $productTransaction)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['buyer'])) {
            if ($productTransaction->user_id !== $user->id) {
                abort(403, 'Anda tidak dapat melihat pesanan milik pengguna lain.');
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

        abort(403, 'Anda tidak memiliki izin melihat detail pesanan.');
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
    public function approve(Request $request, ProductTransaction $productTransaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403, 'Hanya Owner atau Admin yang dapat memproses pesanan.');

        if ($productTransaction->status !== ProductTransaction::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Hanya pesanan berstatus menunggu yang bisa disetujui.');
        }

        if (!$productTransaction->proof) {
            return redirect()->back()->with('error', 'Bukti pembayaran belum diunggah. Pesanan tidak dapat diproses sebelum bukti diverifikasi.');
        }

        $request->validate([
            'tax_amount' => 'nullable|integer|min:0',
            'insurance_amount' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transaction = ProductTransaction::with(['user', 'transactionDetails'])
                ->lockForUpdate()
                ->findOrFail($productTransaction->id);

            if ($transaction->status !== ProductTransaction::STATUS_PENDING) {
                throw new \Exception('Hanya pesanan berstatus menunggu yang bisa disetujui.');
            }

            $productIds = $transaction->transactionDetails->pluck('product_id')->all();
            $products = Product::withStock()
                ->lockForUpdate()
                ->whereKey($productIds)
                ->get()
                ->keyBy('id');

            foreach ($transaction->transactionDetails as $detail) {
                $product = $products[$detail->product_id] ?? null;
                if (!$product || $product->stock < $detail->qty) {
                    throw new \Exception("Stok produk {$product?->name} tidak mencukupi!");
                }
            }

            // Allow manual override of tax and insurance
            $taxAmount = $request->filled('tax_amount') ? (int) $request->tax_amount : $transaction->tax_amount;
            $insuranceAmount = $request->filled('insurance_amount') ? (int) $request->insurance_amount : $transaction->insurance_amount;

            // Recalculate total if tax/insurance changed
            $subTotal = $transaction->transactionDetails->sum(fn($d) => $d->price * $d->qty);
            $newTotal = $subTotal + $taxAmount + $insuranceAmount + $transaction->shipping_cost;

            $transaction->update([
                'is_paid' => true,
                'status'  => ProductTransaction::STATUS_PROCESSING,
                'tax_amount' => $taxAmount,
                'insurance_amount' => $insuranceAmount,
                'total_amount' => $newTotal,
            ]);

            DB::commit();

            OrderNotifications::orderStatusChanged($transaction);
            AuditLogger::log('order.approved', $transaction);

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
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403, 'Hanya Owner atau Admin yang dapat memproses pesanan.');

        if ($productTransaction->status !== ProductTransaction::STATUS_PROCESSING) {
            return redirect()->back()->with('error', 'Hanya pesanan berstatus diproses yang bisa dikirim.');
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        try {
            $transaction = DB::transaction(function () use ($productTransaction, $validated) {
                $transaction = ProductTransaction::with('user')
                    ->lockForUpdate()
                    ->findOrFail($productTransaction->id);

                if ($transaction->status !== ProductTransaction::STATUS_PROCESSING) {
                    throw new \Exception('Hanya pesanan berstatus diproses yang bisa dikirim.');
                }

                $transaction->update([
                    'status' => ProductTransaction::STATUS_SHIPPED,
                    'tracking_number' => $validated['tracking_number'],
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $waMessage = "Halo {$transaction->user->name}, pesanan #{$transaction->id} sudah dikirim via {$transaction->shipping_method}. Nomor resi: {$validated['tracking_number']}. Terima kasih!";

        OrderNotifications::orderStatusChanged($transaction);
        AuditLogger::log('order.shipped', $transaction, ['tracking' => $validated['tracking_number']]);

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
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403, 'Hanya Owner atau Admin yang dapat memproses pesanan.');

        if ($productTransaction->status !== ProductTransaction::STATUS_SHIPPED) {
            return redirect()->back()->with('error', 'Pesanan harus berstatus dikirim sebelum diselesaikan.');
        }

        try {
            $transaction = DB::transaction(function () use ($productTransaction) {
                $transaction = ProductTransaction::with('user')
                    ->lockForUpdate()
                    ->findOrFail($productTransaction->id);

                if ($transaction->status !== ProductTransaction::STATUS_SHIPPED) {
                    throw new \Exception('Pesanan harus berstatus dikirim sebelum diselesaikan.');
                }

                $transaction->update([
                    'status' => ProductTransaction::STATUS_COMPLETED,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $waMessage = "Halo {$transaction->user->name}, pesanan #{$transaction->id} telah selesai. Terima kasih sudah berbelanja di Wigati Buku.";

        OrderNotifications::orderStatusChanged($transaction);
        AuditLogger::log('order.completed', $transaction);

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
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403, 'Hanya Owner atau Admin yang dapat memproses pesanan.');

        if ($productTransaction->status !== ProductTransaction::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Hanya pesanan berstatus menunggu yang bisa ditolak.');
        }

        $validated = $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        try {
            $transaction = DB::transaction(function () use ($productTransaction, $validated) {
                $transaction = ProductTransaction::with(['user', 'transactionDetails.product'])
                    ->lockForUpdate()
                    ->findOrFail($productTransaction->id);

                if ($transaction->status !== ProductTransaction::STATUS_PENDING) {
                    throw new \Exception('Hanya pesanan berstatus menunggu yang bisa ditolak.');
                }

                $transaction->update([
                    'status' => ProductTransaction::STATUS_REJECTED,
                    'rejection_note' => $validated['rejection_note'],
                ]);

                foreach ($transaction->transactionDetails as $detail) {
                    $detail->product->stockMutations()->create([
                        'type' => 'in',
                        'quantity' => $detail->qty,
                        'description' => 'Stok dilepas karena order #' . $transaction->id . ' ditolak.',
                    ]);
                }

                return $transaction;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $waMessage = "Halo {$transaction->user->name}, mohon maaf pesanan #{$transaction->id} terpaksa kami tolak. Alasan: {$validated['rejection_note']}.";

        OrderNotifications::orderStatusChanged($transaction);
        AuditLogger::log('order.rejected', $transaction);

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
            abort_unless($productTransaction->user_id === $user->id, 403, 'Anda hanya bisa membatalkan pesanan milik Anda.');
            abort_unless($productTransaction->status === ProductTransaction::STATUS_PENDING, 403, 'Pesanan hanya bisa dibatalkan saat berstatus menunggu.');
} elseif (!$user->hasAnyRole(['owner', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin membatalkan pesanan.');
        }

        $stockHasBeenDeducted = [
            ProductTransaction::STATUS_PENDING,
            ProductTransaction::STATUS_PROCESSING,
            ProductTransaction::STATUS_SHIPPED,
            ProductTransaction::STATUS_COMPLETED,
        ];

        DB::beginTransaction();
        try {
            $transaction = ProductTransaction::with(['transactionDetails.product'])
                ->lockForUpdate()
                ->findOrFail($productTransaction->id);

            if ($transaction->status === ProductTransaction::STATUS_CANCELLED) {
                throw new \Exception('Pesanan sudah dibatalkan.');
            }

            $statusBeforeCancel = $transaction->status;
            $transaction->update(['status' => ProductTransaction::STATUS_CANCELLED]);

            if (in_array($statusBeforeCancel, $stockHasBeenDeducted, true)) {
                foreach ($transaction->transactionDetails as $detail) {
                    $detail->product->stockMutations()->create([
                        'type'        => 'in',
                        'quantity'    => $detail->qty,
                        'description' => 'Stok kembali karena pembatalan order #' . $transaction->id,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }

        AuditLogger::log('order.cancelled', $transaction, ['from_status' => $statusBeforeCancel]);

        if (!$user->hasRole('buyer')) {
            OrderNotifications::orderStatusChanged($transaction);
        }

        return redirect()->route('product_transactions.index')->with('success', 'Order berhasil dibatalkan.');
    }

    /**
     * Unggah bukti pembayaran setelah checkout (pesanan pending tanpa bukti).
     */
    public function uploadProof(Request $request, ProductTransaction $productTransaction)
    {
        $user = auth()->user();

        abort_unless(
            $user->hasRole('buyer') && $productTransaction->user_id === $user->id,
            403,
            'Anda hanya dapat mengunggah bukti untuk pesanan milik Anda.'
        );
        abort_unless(
            $productTransaction->status === ProductTransaction::STATUS_PENDING,
            403,
            'Bukti hanya bisa diunggah saat pesanan berstatus menunggu.'
        );

        $request->validate([
            'proof' => 'required|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        $path = $request->file('proof')->store('payment_proofs', 'public');
        $productTransaction->update(['proof' => $path]);

        OrderNotifications::proofUploaded($productTransaction);
        AuditLogger::log('order.proof_uploaded', $productTransaction);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Pesanan akan diproses setelah diverifikasi.');
    }
}
