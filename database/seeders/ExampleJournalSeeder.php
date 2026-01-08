<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use Carbon\Carbon;

class ExampleJournalSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;
        $fy = FiscalYear::where('company_id', $companyId)->first();
        
        // Helper to get Account ID by Code
        $code = fn($c) => ChartOfAccount::where('code', $c)->first()->id;

        $entries = [
            [
                'desc' => 'Cash Sales - Invoice #001',
                'date' => '2025-01-05',
                'items' => [
                    ['acc' => '1001', 'debit' => 5000, 'credit' => 0], // Cash
                    ['acc' => '4001', 'debit' => 0, 'credit' => 5000], // Sales
                ]
            ],
            [
                'desc' => 'Office Rent Payment (January)',
                'date' => '2025-01-05',
                'items' => [
                    ['acc' => '5001', 'debit' => 12000, 'credit' => 0], // Rent Exp
                    ['acc' => '1001', 'debit' => 0, 'credit' => 12000], // Cash
                ]
            ],
            [
                'desc' => 'Cash Deposit to Bank',
                'date' => '2025-01-06',
                'items' => [
                    ['acc' => '1002', 'debit' => 25000, 'credit' => 0], // Bank
                    ['acc' => '1001', 'debit' => 0, 'credit' => 25000], // Cash
                ]
            ],
            [
                'desc' => 'Purchase of Goods (Cash)',
                'date' => '2025-01-07',
                'items' => [
                    ['acc' => '5003', 'debit' => 8000, 'credit' => 0], // Purchase
                    ['acc' => '1001', 'debit' => 0, 'credit' => 8000], // Cash
                ]
            ],
            [
                'desc' => 'Credit Sales to Customer A',
                'date' => '2025-01-08',
                'items' => [
                    ['acc' => '1200', 'debit' => 15000, 'credit' => 0], // AR
                    ['acc' => '4001', 'debit' => 0, 'credit' => 15000], // Sales
                ]
            ],
            [
                'desc' => 'Additional Owner Capital',
                'date' => '2025-01-10',
                'items' => [
                    ['acc' => '1002', 'debit' => 100000, 'credit' => 0], // Bank
                    ['acc' => '3001', 'debit' => 0, 'credit' => 100000], // Equity
                ]
            ],
            [
                'desc' => 'Salary Payment (Staff 1)',
                'date' => '2025-01-15',
                'items' => [
                    ['acc' => '5002', 'debit' => 8000, 'credit' => 0], // Salary
                    ['acc' => '1002', 'debit' => 0, 'credit' => 8000], // Bank
                ]
            ],
            [
                'desc' => 'Received from Customer A',
                'date' => '2025-01-20',
                'items' => [
                    ['acc' => '1002', 'debit' => 10000, 'credit' => 0], // Bank
                    ['acc' => '1200', 'debit' => 0, 'credit' => 10000], // AR
                ]
            ],
            [
                'desc' => 'Utilities Payment',
                'date' => '2025-01-25',
                'items' => [
                    ['acc' => '5001', 'debit' => 3000, 'credit' => 0], // Rent/Exp (using Rent for now as generic exp)
                    ['acc' => '1001', 'debit' => 0, 'credit' => 3000], // Cash
                ]
            ],
            [
                'desc' => 'Purchase on Credit from Supplier X',
                'date' => '2025-01-28',
                'items' => [
                    ['acc' => '5003', 'debit' => 20000, 'credit' => 0], // Purchase
                    ['acc' => '2001', 'debit' => 0, 'credit' => 20000], // AP
                ]
            ],
        ];

        foreach ($entries as $index => $data) {
            $je = JournalEntry::create([
                'company_id' => $companyId,
                'fiscal_year_id' => $fy->id,
                'entry_number' => 'JE-AUTO-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'date' => $data['date'],
                'description' => $data['desc'],
                'total_amount' => collect($data['items'])->sum('debit'),
                'status' => 'posted'
            ]);

            foreach ($data['items'] as $item) {
                $je->items()->create([
                    'account_id' => $code($item['acc']),
                    'debit' => $item['debit'],
                    'credit' => $item['credit']
                ]);
            }
        }
    }
}
