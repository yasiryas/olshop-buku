<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('articles', 'is_published')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->boolean('is_published')->default(false)->after('category_id');
            });
        }

        if (! Schema::hasColumn('articles', 'published_at')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            });
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'published_at']);
        });
    }
};