<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_transactions', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });

        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->index(['product_id', 'type']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('is_active');
        });

        // Bersihkan duplikat keranjang sebelum unique index dipasang (jagakan firstOrCreate race).
        if (Schema::hasTable('carts')) {
            $duplicates = DB::table('carts')
                ->select('user_id', 'product_id')
                ->groupBy('user_id', 'product_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $dup) {
                DB::table('carts')
                    ->where('user_id', $dup->user_id)
                    ->where('product_id', $dup->product_id)
                    ->orderBy('id')
                    ->skip(1)
                    ->delete();
            }

            Schema::table('carts', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_transactions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'type']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });
    }
};