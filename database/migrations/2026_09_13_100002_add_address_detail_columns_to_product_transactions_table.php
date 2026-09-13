<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_transactions', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('phone_number');
            $table->string('province')->nullable()->after('recipient_name');
            $table->string('district')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('product_transactions', function (Blueprint $table) {
            $table->dropColumn(['recipient_name', 'province', 'district']);
        });
    }
};