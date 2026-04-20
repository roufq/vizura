<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('status');
            $table->index('type');
            $table->index('posted_at');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('status');
            $table->index('received_at');
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->index('posted_at');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('action');
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['posted_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['received_at']);
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex(['posted_at']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
            $table->dropIndex(['occurred_at']);
        });
    }
};
