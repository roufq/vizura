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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('paid_total', 15, 2)->default(0)->after('payment_method');
            $table->decimal('payable_balance', 15, 2)->default(0)->after('paid_total');
            $table->string('payment_status', 20)->default('paid')->after('payable_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['paid_total', 'payable_balance', 'payment_status']);
        });
    }
};
