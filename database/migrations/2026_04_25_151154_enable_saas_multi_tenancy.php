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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->enum('plan', ['starter', 'business', 'enterprise'])->default('starter');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $tables = [
            'users',
            'locations',
            'categories',
            'units',
            'products',
            'suppliers',
            'customers',
            'purchases',
            'sales',
            'expenses',
            'accounts',
            'journals',
            'audit_logs',
            'stock_adjustments',
            'stock_transfers'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'locations',
            'categories',
            'units',
            'products',
            'suppliers',
            'customers',
            'purchases',
            'sales',
            'expenses',
            'accounts',
            'journals',
            'audit_logs',
            'stock_adjustments',
            'stock_transfers'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        Schema::dropIfExists('tenants');
    }
};
