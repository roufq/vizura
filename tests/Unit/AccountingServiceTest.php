<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Location;
use App\Support\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_journal_rejects_unbalanced_lines(): void
    {
        $location = Location::factory()->create();

        $cash = Account::create([
            'code' => '1001',
            'name' => 'Cash',
            'type' => 'asset',
            'is_cash' => true,
            'is_active' => true,
        ]);

        $revenue = Account::create([
            'code' => '4001',
            'name' => 'Revenue',
            'type' => 'income',
            'is_cash' => false,
            'is_active' => true,
        ]);

        $service = new AccountingService;

        $this->expectException(ValidationException::class);

        $service->createJournal([
            'location_id' => $location->id,
            'reference_no' => 'JRN-001',
            'description' => 'Unbalanced',
            'posted_at' => now(),
        ], [
            [
                'account_id' => $cash->id,
                'debit' => 100,
                'credit' => 0,
            ],
            [
                'account_id' => $revenue->id,
                'debit' => 0,
                'credit' => 50,
            ],
        ]);
    }
}
