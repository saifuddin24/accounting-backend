<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\ChartOfAccount;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Company
        $company = Company::create([
            'name' => 'Demo Trading Co.',
            'currency_code' => 'BDT',
        ]);

        // 2. Create Fiscal Year
        FiscalYear::create([
            'company_id' => $company->id,
            'name' => 'FY 2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_closed' => false,
        ]);

        // 3. Create Basic Chart of Accounts
        $accounts = [
            // Assets
            ['code' => '1001', 'name' => 'Cash in Hand', 'type' => 'Asset', 'normal_balance' => 'debit'],
            ['code' => '1002', 'name' => 'Bank Account', 'type' => 'Asset', 'normal_balance' => 'debit'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'Asset', 'normal_balance' => 'debit'],
            
            // Liabilities
            ['code' => '2001', 'name' => 'Accounts Payable', 'type' => 'Liability', 'normal_balance' => 'credit'],
            
            // Equity
            ['code' => '3001', 'name' => 'Owner Capital', 'type' => 'Equity', 'normal_balance' => 'credit'],
            ['code' => '3002', 'name' => 'Retained Earnings', 'type' => 'Equity', 'normal_balance' => 'credit'],
            
            // Income
            ['code' => '4001', 'name' => 'Sales Revenue', 'type' => 'Income', 'normal_balance' => 'credit'],
            
            // Expenses
            ['code' => '5001', 'name' => 'Office Rent', 'type' => 'Expense', 'normal_balance' => 'debit'],
            ['code' => '5002', 'name' => 'Salary Expense', 'type' => 'Expense', 'normal_balance' => 'debit'],
            ['code' => '5003', 'name' => 'Purchase Account', 'type' => 'Expense', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::create(array_merge($acc, ['company_id' => $company->id]));
        }
    }
}
