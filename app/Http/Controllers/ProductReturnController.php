<?php

namespace App\Http\Controllers;

use App\Models\ProductReturn;
use App\Models\ProductTransaction;
use Illuminate\Http\Request;

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
            ->orderByRaw("FIELD(status, 'requested', 'approved', 'rejected')")
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
        abort_unless($user->hasRole('buyer') && $productTransaction->user_id === $user->id, 403);
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
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403);
        abort_unless($productReturn->status === ProductReturn::STATUS_REQUESTED, 403, 'Hanya retur berstatus menunggu yang bisa disetujui.');

        $transaction = $productReturn->transaction()->with('transactionDetails.product')->firstOrFail();

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($transaction->transactionDetails as $detail) {
                $detail->product->stockMutations()->create([
                    'type' => 'in',
                    'quantity' => $detail->qty,
                    'description' => 'Stok masuk dari retur pesanan #' . $transaction->id,
                ]);
            }

            $productReturn->update([
                'status' => ProductReturn::STATUS_APPROVED,
                'admin_note' => 'Retur disetujui, stok dikembalikan.',
            ]);

            $transaction->update(['status' => ProductTransaction::STATUS_RETURNED]);

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Retur #' . $productReturn->id . ' disetujui, stok dikembalikan.');
    }

    public function reject(Request $request, ProductReturn $productReturn)
    {
        abort_unless(auth()->user()->hasAnyRole(['owner', 'admin']), 403);
        abort_unless($productReturn->status === ProductReturn::STATUS_REQUESTED, 403, 'Hanya retur berstatus menunggu yang bisa ditolak.');

        $validated = $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $productReturn->update([
            'status' => ProductReturn::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'],
        ]);

        return redirect()->back()->with('success', 'Pengajuan retur #' . $productReturn->id . ' ditolak.');
    }
}