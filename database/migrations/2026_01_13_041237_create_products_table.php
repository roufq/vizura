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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('block_when_out_of_stock')->default(false);
            $table->string('batch_code')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
