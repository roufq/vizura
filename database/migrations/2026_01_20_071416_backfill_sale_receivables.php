<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sales = DB::table('sales')
            ->select('sales.id', 'sales.total')
            ->where('sales.type', 'sale')
            ->where('sales.status', 'posted')
            ->get();

        foreach ($sales as $sale) {
            $receivable = (float) DB::table('sale_payments')
                ->where('sale_id', $sale->id)
                ->where('method', 'piutang')
                ->sum('amount');

            if ($receivable > 0) {
                $paidTotal = max(0, (float) $sale->total - $receivable);
                DB::table('sales')
                    ->where('id', $sale->id)
                    ->update([
                        'receivable_balance' => $receivable,
                        'paid_total' => $paidTotal,
                        'payment_status' => $receivable > 0 ? 'partial' : 'paid',
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sales')
            ->where('type', 'sale')
            ->where('status', 'posted')
            ->update([
                'receivable_balance' => 0,
                'payment_status' => 'paid',
            ]);
    }
};
