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
            $table->string('shipping_method')->nullable()->after('status');
            $table->unsignedInteger('shipping_cost')->default(0)->after('shipping_method');
            $table->string('payment_method')->nullable()->after('shipping_cost');
            $table->string('tracking_number')->nullable()->after('payment_method');
            $table->text('rejection_note')->nullable()->after('tracking_number');
        });

        DB::table('product_transactions')
            ->where('status', 'approved')
            ->update(['status' => 'processing']);

        DB::table('product_transactions')
            ->whereIn('status', ['processing', 'shipped', 'completed'])
            ->update(['is_paid' => true]);
    }

    public function down(): void
    {
        DB::table('product_transactions')
            ->whereIn('status', ['processing', 'shipped', 'completed'])
            ->update(['is_paid' => false]);

        Schema::table('product_transactions', function (Blueprint $table) {
            $table->dropColumn(['shipping_method', 'shipping_cost', 'payment_method', 'tracking_number', 'rejection_note']);
        });
    }
};