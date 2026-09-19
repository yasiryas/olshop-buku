<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\ProductTransaction;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReturnController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $returns = ProductReturn::with(['transaction.user'])
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('reason', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('transaction', function ($qt) use ($search) {
                        $qt->where('id', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', "%{$search}%"));
                    });
            }))
            ->orderByRaw("CASE WHEN status = 'requested' THEN 0 WHEN status = 'approved' THEN 1 WHEN status = 'rejected' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.partials.returns_list', compact('returns', 'search'));
        }

        return view('admin.returns.index', compact('returns', 'search'));
    }

    public function store(Request $request, ProductTransaction $productTransaction)
    {
        $user = $request->user();
        abort_unless($user->hasRole('buyer') && $productTransaction->user_id === $user->id, 403, 'Hanya pembeli yang bisa mengajukan retur untuk pesanan miliknya.');
        abort_unless($productTransaction->status === ProductTransaction::STATUS_COMPLETED, 403, 'Retur hanya bisa diajukan untuk pesanan selesai.');
        abort_if($productTransaction->returns()->whereIn('status', [ProductReturn::STATUS_REQUESTED, ProductReturn::STATUS_APPROVED])->exists(), 403, 'Sudah ada pengajuan retur untuk pesanan ini.');

        $validated = $request->validate([
            'reason' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $productTransaction->returns()->create([
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => ProductReturn::STATUS_REQUESTED,
        ]);

        return redirect()
            ->route('product_transactions.show', $productTransaction->id)
            ->with('success', 'Pengajuan retur terkirim. Menunggu konfirmasi admin.');
    }

    public function approve(ProductReturn $productReturn)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403, 'Hanya Owner atau Admin yang dapat memproses retur.');

        DB::beginTransaction();
        try {
            $return = ProductReturn::with(['transaction.transactionDetails.product'])
                ->lockForUpdate()
                ->findOrFail($productReturn->id);

            if ($return->status !== ProductReturn::STATUS_REQUESTED) {
                throw new \Exception('Hanya retur berstatus menunggu yang bisa disetujui.');
            }

            $transaction = $return->transaction;

            $productIds = $transaction->transactionDetails->pluck('product_id')->all();
            $products = Product::lockForUpdate()->whereKey($productIds)->get()->keyBy('id');

            foreach ($transaction->transactionDetails as $detail) {
                $products[$detail->product_id]->stockMutations()->create([
                    'type' => 'in',
                    'quantity' => $detail->qty,
                    'description' => 'Stok masuk dari retur pesanan #' . $transaction->id,
                ]);
            }

            $return->update([
                'status' => ProductReturn::STATUS_APPROVED,
                'admin_note' => 'Retur disetujui, stok dikembalikan.',
            ]);

            $transaction->update(['status' => ProductTransaction::STATUS_RETURNED]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }

        AuditLogger::log('return.approved', $productReturn);

        return redirect()->back()->with('success', 'Retur #' . $productReturn->id . ' disetujui, stok dikembalikan.');
    }

    public function reject(Request $request, ProductReturn $productReturn)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403, 'Hanya Owner atau Admin yang dapat memproses retur.');
        abort_unless($productReturn->status === ProductReturn::STATUS_REQUESTED, 403, 'Hanya retur berstatus menunggu yang bisa ditolak.');

        $validated = $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $productReturn->update([
            'status' => ProductReturn::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'],
        ]);

        AuditLogger::log('return.rejected', $productReturn);

        return redirect()->back()->with('success', 'Pengajuan retur #' . $productReturn->id . ' ditolak.');
    }
}