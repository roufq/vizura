<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Drop old unique constraint (depends on its name, usually locations_code_unique)
            $table->dropUnique(['code']);
            
            // Add new composite unique constraint
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique(['code']);
        });
    }
};
