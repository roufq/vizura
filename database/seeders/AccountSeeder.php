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
            ['code' => '1001', 'name' => 'Cash', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1002', 'name' => 'Bank', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1101', 'name' => 'Receivables', 'type' => 'asset', 'is_cash' => false],
            ['code' => '1201', 'name' => 'Inventory', 'type' => 'asset', 'is_cash' => false],
            ['code' => '2001', 'name' => 'Payables', 'type' => 'liability', 'is_cash' => false],
            ['code' => '4001', 'name' => 'Revenue', 'type' => 'income', 'is_cash' => false],
            ['code' => '5001', 'name' => 'COGS', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6001', 'name' => 'Salary', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6002', 'name' => 'Rent', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6003', 'name' => 'Utilities', 'type' => 'expense', 'is_cash' => false],
            ['code' => '6004', 'name' => 'Operational Expenses', 'type' => 'expense', 'is_cash' => false],
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
