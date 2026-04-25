<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values
        DB::table('saas_configs')->insert([
            ['key' => 'upgrade_pstore_url', 'value' => 'https://p-store.net'],
            ['key' => 'upgrade_wa_number', 'value' => '6281234567890'],
            ['key' => 'upgrade_message', 'value' => 'Wah, bisnis Anda berkembang pesat! Upgrade ke paket yang lebih tinggi untuk membuka batasan outlet dan fitur premium lainnya.'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_configs');
    }
};
