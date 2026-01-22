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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('original_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->string('reference_no', 50)->unique();
            $table->string('type', 20)->default('sale');
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('order_discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->boolean('is_tax_inclusive')->default(false);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_total', 15, 2)->default(0);
            $table->decimal('change_due', 15, 2)->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->string('return_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
