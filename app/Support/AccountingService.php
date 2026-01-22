<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Journal;
use App\Models\JournalLine;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\SalePaymentSettlement;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingService
{
    public const ACCOUNT_CASH = '1001';
    public const ACCOUNT_BANK = '1002';
    public const ACCOUNT_RECEIVABLE = '1101';
    public const ACCOUNT_INVENTORY = '1201';
    public const ACCOUNT_REVENUE = '4001';
    public const ACCOUNT_COGS = '5001';
    public const ACCOUNT_PAYABLE = '2001';

    /**
     * @param array<int, array{account_id:int, debit:float, credit:float, memo?:string}> $lines
     */
    public function createJournal(array $data, array $lines): Journal
    {
        $this->ensureBalanced($lines);

        return DB::transaction(function () use ($data, $lines): Journal {
            $journal = Journal::create($data);

            $lineRows = collect($lines)
                ->map(fn (array $line): array => [
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'memo' => $line['memo'] ?? null,
                ])
                ->all();

            $journal->lines()->createMany($lineRows);

            return $journal;
        });
    }

    public function recordSale(Sale $sale, array $paymentLines, float $cogsTotal, ?string $description = null): Journal
    {
        $revenueAccount = $this->getAccountByCode(self::ACCOUNT_REVENUE);
        $inventoryAccount = $this->getAccountByCode(self::ACCOUNT_INVENTORY);
        $cogsAccount = $this->getAccountByCode(self::ACCOUNT_COGS);

        $lines = [];

        foreach ($paymentLines as $payment) {
            $account = $this->resolvePaymentAccount($payment['method'] ?? 'cash');
            $lines[] = [
                'account_id' => $account->id,
                'debit' => $payment['amount'],
                'credit' => 0,
                'memo' => strtoupper((string) $payment['method']),
            ];
        }

        $lines[] = [
            'account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => (float) $sale->total,
            'memo' => 'Pendapatan',
        ];

        if ($cogsTotal > 0) {
            $lines[] = [
                'account_id' => $cogsAccount->id,
                'debit' => $cogsTotal,
                'credit' => 0,
                'memo' => 'HPP',
            ];
            $lines[] = [
                'account_id' => $inventoryAccount->id,
                'debit' => 0,
                'credit' => $cogsTotal,
                'memo' => 'Persediaan',
            ];
        }

        return $this->createJournal([
            'location_id' => $sale->location_id,
            'reference_no' => $sale->reference_no,
            'description' => $description ?? 'Penjualan',
            'sale_id' => $sale->id,
            'created_by' => $sale->cashier_id,
            'posted_at' => $sale->posted_at ?? now(),
        ], $lines);
    }

    public function recordSaleReversal(Sale $sale, array $paymentLines, float $cogsTotal, string $description): Journal
    {
        $revenueAccount = $this->getAccountByCode(self::ACCOUNT_REVENUE);
        $inventoryAccount = $this->getAccountByCode(self::ACCOUNT_INVENTORY);
        $cogsAccount = $this->getAccountByCode(self::ACCOUNT_COGS);
        $amount = abs((float) $sale->total);

        $lines = [];

        foreach ($paymentLines as $payment) {
            $account = $this->resolvePaymentAccount($payment['method'] ?? 'cash');
            $lines[] = [
                'account_id' => $account->id,
                'debit' => 0,
                'credit' => abs((float) $payment['amount']),
                'memo' => 'Pembalikan '.strtoupper((string) $payment['method']),
            ];
        }

        $lines[] = [
            'account_id' => $revenueAccount->id,
            'debit' => $amount,
            'credit' => 0,
            'memo' => 'Pembalikan Pendapatan',
        ];

        if ($cogsTotal > 0) {
            $lines[] = [
                'account_id' => $inventoryAccount->id,
                'debit' => $cogsTotal,
                'credit' => 0,
                'memo' => 'Pembalikan Persediaan',
            ];
            $lines[] = [
                'account_id' => $cogsAccount->id,
                'debit' => 0,
                'credit' => $cogsTotal,
                'memo' => 'Pembalikan HPP',
            ];
        }

        return $this->createJournal([
            'location_id' => $sale->location_id,
            'reference_no' => $sale->reference_no,
            'description' => $description,
            'sale_id' => $sale->id,
            'created_by' => $sale->cashier_id,
            'posted_at' => now(),
        ], $lines);
    }

    public function recordPurchase(Purchase $purchase, ?string $description = null): Journal
    {
        $inventoryAccount = $this->getAccountByCode(self::ACCOUNT_INVENTORY);
        $paymentAccount = $this->resolvePurchaseAccount($purchase->payment_method);

        $lines = [
            [
                'account_id' => $inventoryAccount->id,
                'debit' => (float) $purchase->total,
                'credit' => 0,
                'memo' => 'Persediaan',
            ],
            [
                'account_id' => $paymentAccount->id,
                'debit' => 0,
                'credit' => (float) $purchase->total,
                'memo' => 'Pembayaran',
            ],
        ];

        return $this->createJournal([
            'location_id' => $purchase->location_id,
            'reference_no' => $purchase->reference_no,
            'description' => $description ?? 'Pembelian',
            'purchase_id' => $purchase->id,
            'created_by' => $purchase->received_by,
            'posted_at' => $purchase->received_at ?? now(),
        ], $lines);
    }

    public function recordExpense(Expense $expense, ?string $description = null): Journal
    {
        $lines = [
            [
                'account_id' => $expense->account_id,
                'debit' => (float) $expense->amount,
                'credit' => 0,
                'memo' => 'Biaya Operasional',
            ],
            [
                'account_id' => $expense->payment_account_id,
                'debit' => 0,
                'credit' => (float) $expense->amount,
                'memo' => 'Pembayaran',
            ],
        ];

        return $this->createJournal([
            'location_id' => $expense->location_id,
            'reference_no' => $expense->reference_no,
            'description' => $description ?? 'Biaya Operasional',
            'expense_id' => $expense->id,
            'created_by' => $expense->created_by,
            'posted_at' => $expense->expense_date,
        ], $lines);
    }

    public function recordPurchasePayment(PurchasePayment $payment, ?string $description = null): Journal
    {
        $payableAccount = $this->getAccountByCode(self::ACCOUNT_PAYABLE);
        $paymentAccount = $this->resolvePaymentAccount($payment->method);

        $lines = [
            [
                'account_id' => $payableAccount->id,
                'debit' => (float) $payment->amount,
                'credit' => 0,
                'memo' => 'Pelunasan Hutang',
            ],
            [
                'account_id' => $paymentAccount->id,
                'debit' => 0,
                'credit' => (float) $payment->amount,
                'memo' => 'Pembayaran',
            ],
        ];

        return $this->createJournal([
            'location_id' => $payment->location_id,
            'reference_no' => $payment->reference_no,
            'description' => $description ?? 'Pembayaran Hutang',
            'purchase_id' => $payment->purchase_id,
            'created_by' => $payment->created_by,
            'posted_at' => $payment->paid_at ?? now(),
        ], $lines);
    }

    public function recordReceivablePayment(SalePaymentSettlement $payment, ?string $description = null): Journal
    {
        $receivableAccount = $this->getAccountByCode(self::ACCOUNT_RECEIVABLE);
        $paymentAccount = $this->resolvePaymentAccount($payment->method);

        $lines = [
            [
                'account_id' => $paymentAccount->id,
                'debit' => (float) $payment->amount,
                'credit' => 0,
                'memo' => 'Pelunasan Piutang',
            ],
            [
                'account_id' => $receivableAccount->id,
                'debit' => 0,
                'credit' => (float) $payment->amount,
                'memo' => 'Piutang',
            ],
        ];

        return $this->createJournal([
            'location_id' => $payment->location_id,
            'reference_no' => $payment->reference_no,
            'description' => $description ?? 'Pembayaran Piutang',
            'sale_id' => $payment->sale_id,
            'created_by' => $payment->created_by,
            'posted_at' => $payment->paid_at ?? now(),
        ], $lines);
    }

    public function resolvePaymentAccount(string $method): Account
    {
        return match ($method) {
            'cash' => $this->getAccountByCode(self::ACCOUNT_CASH),
            'piutang' => $this->getAccountByCode(self::ACCOUNT_RECEIVABLE),
            default => $this->getAccountByCode(self::ACCOUNT_BANK),
        };
    }

    public function resolvePurchaseAccount(?string $method): Account
    {
        return match ($method) {
            'cash' => $this->getAccountByCode(self::ACCOUNT_CASH),
            'bank' => $this->getAccountByCode(self::ACCOUNT_BANK),
            default => $this->getAccountByCode(self::ACCOUNT_PAYABLE),
        };
    }

    public function getAccountByCode(string $code): Account
    {
        $account = Account::query()->where('code', $code)->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'account' => 'Akun dengan kode '.$code.' tidak ditemukan.',
            ]);
        }

        return $account;
    }

    /**
     * @param array<int, array{account_id:int, debit:float, credit:float}> $lines
     */
    private function ensureBalanced(array $lines): void
    {
        $debit = collect($lines)->sum('debit');
        $credit = collect($lines)->sum('credit');

        if (abs($debit - $credit) > 0.01) {
            throw ValidationException::withMessages([
                'journal' => 'Jurnal tidak seimbang.',
            ]);
        }
    }
}
