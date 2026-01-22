<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['code' => '1001', 'name' => 'Kas', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1002', 'name' => 'Bank', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1101', 'name' => 'Piutang', 'type' => 'asset', 'is_cash' => false],
            ['code' => '1201', 'name' => 'Persediaan', 'type' => 'asset', 'is_cash' => false],
            ['code' => '2001', 'name' => 'Hutang', 'type' => 'liability', 'is_cash' => false],
            ['code' => '4001', 'name' => 'Pendapatan', 'type' => 'income', 'is_cash' => false],
            ['code' => '5001', 'name' => 'HPP', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6001', 'name' => 'Gaji', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6002', 'name' => 'Sewa', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6003', 'name' => 'Listrik', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6004', 'name' => 'Biaya Operasional', 'type' => 'expense', 'is_cash' => false],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'is_cash' => $account['is_cash'],
                    'is_active' => true,
                ]
            );
        }
    }
}
