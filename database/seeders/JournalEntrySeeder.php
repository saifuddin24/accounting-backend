<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        $companyId    = 1;
        $fiscalYearId = 2;

        $accounts = DB::table('chart_of_accounts')
            ->where('company_id', $companyId)
            ->get()
            ->keyBy('name');

        // 🔹 GET TRANSACTIONS ONCE
        $transactions = $this->getTransactions();

        for ($i = 1; $i <= 500; $i++) {

            // 🔥 EXACT PLACE YOU ASKED ABOUT (START)
            $key = collect(array_keys($transactions))->random();
            $trx = $transactions[$key];

            $description   = $trx['description'];
            $debitAccount  = $trx['debit_account'];
            $creditAccount = $trx['credit_account'];
            // 🔥 EXACT PLACE YOU ASKED ABOUT (END)

            $amount = rand(1_000, 50_000);
            $date   = now()->subDays(rand(0, 360));

            $entryId = DB::table('journal_entries')->insertGetId([
                'company_id'     => $companyId,
                'fiscal_year_id' => $fiscalYearId,
                'entry_number'   => 'JE-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'date'           => $date->format('Y-m-d'),
                'description'    => $description . ' amounting to ৳' . number_format($amount, 2),
                'reference'      => 'REF-' . strtoupper(Str::random(6)),
                'total_amount'   => $amount,
                'status'         => 'posted',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Debit
            DB::table('journal_items')->insert([
                'journal_entry_id' => $entryId,
                'account_id'       => $accounts[$debitAccount]->id,
                'description'      => $description,
                'debit'            => $amount,
                'credit'           => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Credit
            DB::table('journal_items')->insert([
                'journal_entry_id' => $entryId,
                'account_id'       => $accounts[$creditAccount]->id,
                'description'      => $description,
                'debit'            => 0,
                'credit'           => $amount,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }


    function getTransactions(){

        $rawTransactions = [

            // -------------------------
            // CASH SALES (1–20)
            // -------------------------
            'cash_sale_1' => ['Cash sales received', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_2' => ['Retail cash sale', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_3' => ['Cash sale at counter', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_4' => ['Cash sale – walk-in customer', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_5' => ['Instant cash sale', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_6' => ['Daily cash sales collection', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_7' => ['Spot cash sales', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_8' => ['Cash sale against invoice', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_9' => ['Cash received from direct sale', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_10' => ['Cash sale settlement', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_11' => ['Showroom cash sales', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_12' => ['Local cash sale', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_13' => ['Cash sales receipt', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_14' => ['Immediate cash sales', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_15' => ['Cash sales for the day', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_16' => ['Cash sale – promotional', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_17' => ['Cash sales from shop', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_18' => ['Cash received from sales', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_19' => ['Cash sale transaction', 'Cash in Hand', 'Sales Revenue'],
            'cash_sale_20' => ['Cash sales income', 'Cash in Hand', 'Sales Revenue'],

            // -------------------------
            // BANK SALES / RECEIPTS (21–35)
            // -------------------------
            'bank_sale_21' => ['Sales proceeds received in bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_22' => ['Customer payment received in bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_23' => ['Sales collection via bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_24' => ['Online sales received in bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_25' => ['Bank received sales amount', 'Bank Account', 'Sales Revenue'],
            'bank_sale_26' => ['Bank deposit from sales', 'Bank Account', 'Sales Revenue'],
            'bank_sale_27' => ['Customer transfer received', 'Bank Account', 'Sales Revenue'],
            'bank_sale_28' => ['Sales proceeds credited to bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_29' => ['Sales receipt via bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_30' => ['Bank collection of sales', 'Bank Account', 'Sales Revenue'],
            'bank_sale_31' => ['POS sales received in bank', 'Bank Account', 'Sales Revenue'],
            'bank_sale_32' => ['Digital sales receipt', 'Bank Account', 'Sales Revenue'],
            'bank_sale_33' => ['Card sales settlement', 'Bank Account', 'Sales Revenue'],
            'bank_sale_34' => ['Sales amount deposited', 'Bank Account', 'Sales Revenue'],
            'bank_sale_35' => ['Sales payment cleared in bank', 'Bank Account', 'Sales Revenue'],

            // -------------------------
            // CREDIT SALES / RECEIVABLES (36–50)
            // -------------------------
            'credit_sale_36' => ['Goods sold on credit', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_37' => ['Credit sales to customer', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_38' => ['Sales made on credit basis', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_39' => ['Invoice raised for credit sale', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_40' => ['Customer billed on credit', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_41' => ['Credit sales transaction', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_42' => ['Sales booked on credit', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_43' => ['Credit invoice generated', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_44' => ['Sales due from customer', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_45' => ['Sales on account', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_46' => ['Deferred sales revenue', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_47' => ['Outstanding sales invoice', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_48' => ['Customer receivable created', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_49' => ['Sales billed to customer', 'Accounts Receivable', 'Sales Revenue'],
            'credit_sale_50' => ['Sales made on account credit', 'Accounts Receivable', 'Sales Revenue'],

            // -------------------------
            // PURCHASES & PAYABLES (51–75)
            // -------------------------
            'purchase_51' => ['Goods purchased in cash', 'Purchase Account', 'Cash in Hand'],
            'purchase_52' => ['Cash purchase of goods', 'Purchase Account', 'Cash in Hand'],
            'purchase_53' => ['Office supplies purchased', 'Purchase Account', 'Cash in Hand'],
            'purchase_54' => ['Local purchase paid in cash', 'Purchase Account', 'Cash in Hand'],
            'purchase_55' => ['Purchase paid via bank', 'Purchase Account', 'Bank Account'],
            'purchase_56' => ['Goods purchased through bank', 'Purchase Account', 'Bank Account'],
            'purchase_57' => ['Inventory purchase paid by bank', 'Purchase Account', 'Bank Account'],
            'purchase_58' => ['Credit purchase of goods', 'Purchase Account', 'Accounts Payable'],
            'purchase_59' => ['Purchase on credit', 'Purchase Account', 'Accounts Payable'],
            'purchase_60' => ['Supplier invoice recorded', 'Purchase Account', 'Accounts Payable'],
            'purchase_61' => ['Goods purchased on account', 'Purchase Account', 'Accounts Payable'],
            'purchase_62' => ['Purchase booked from supplier', 'Purchase Account', 'Accounts Payable'],
            'purchase_63' => ['Credit purchase entry', 'Purchase Account', 'Accounts Payable'],
            'purchase_64' => ['Supplier payable created', 'Purchase Account', 'Accounts Payable'],
            'purchase_65' => ['Purchase expense recorded', 'Purchase Account', 'Accounts Payable'],
            'purchase_66' => ['Cash purchase settlement', 'Purchase Account', 'Cash in Hand'],
            'purchase_67' => ['Bank paid purchase bill', 'Purchase Account', 'Bank Account'],
            'purchase_68' => ['Purchase payment cleared', 'Purchase Account', 'Bank Account'],
            'purchase_69' => ['Purchase made for business', 'Purchase Account', 'Cash in Hand'],
            'purchase_70' => ['Daily purchase transaction', 'Purchase Account', 'Cash in Hand'],
            'purchase_71' => ['Goods procurement entry', 'Purchase Account', 'Accounts Payable'],
            'purchase_72' => ['Purchase invoice posted', 'Purchase Account', 'Accounts Payable'],
            'purchase_73' => ['Supplier purchase recorded', 'Purchase Account', 'Accounts Payable'],
            'purchase_74' => ['Trade purchase booked', 'Purchase Account', 'Accounts Payable'],
            'purchase_75' => ['Business purchase expense', 'Purchase Account', 'Cash in Hand'],

            // -------------------------
            // EXPENSES – RENT & SALARY (76–100)
            // -------------------------
            'rent_76' => ['Office rent paid in cash', 'Office Rent', 'Cash in Hand'],
            'rent_77' => ['Monthly office rent paid', 'Office Rent', 'Bank Account'],
            'rent_78' => ['Rent expense for office', 'Office Rent', 'Cash in Hand'],
            'rent_79' => ['Office rent paid via bank', 'Office Rent', 'Bank Account'],
            'rent_80' => ['Premises rent settlement', 'Office Rent', 'Cash in Hand'],

            'salary_81' => ['Staff salary paid in cash', 'Salary Expense', 'Cash in Hand'],
            'salary_82' => ['Monthly salary paid', 'Salary Expense', 'Bank Account'],
            'salary_83' => ['Employee salary disbursement', 'Salary Expense', 'Cash in Hand'],
            'salary_84' => ['Salary payment through bank', 'Salary Expense', 'Bank Account'],
            'salary_85' => ['Salary expense recorded', 'Salary Expense', 'Cash in Hand'],
            'salary_86' => ['Salary settlement for month', 'Salary Expense', 'Bank Account'],
            'salary_87' => ['Staff payroll payment', 'Salary Expense', 'Cash in Hand'],
            'salary_88' => ['Employee wages paid', 'Salary Expense', 'Cash in Hand'],
            'salary_89' => ['Salary cleared via bank', 'Salary Expense', 'Bank Account'],
            'salary_90' => ['Salary paid to employees', 'Salary Expense', 'Cash in Hand'],

            // -------------------------
            // OWNER & EQUITY (101–120)
            // -------------------------
            'capital_101' => ['Capital introduced by owner', 'Cash in Hand', 'Owner Capital'],
            'capital_102' => ['Owner invested cash', 'Cash in Hand', 'Owner Capital'],
            'capital_103' => ['Additional capital injected', 'Bank Account', 'Owner Capital'],
            'capital_104' => ['Owner contribution received', 'Cash in Hand', 'Owner Capital'],
            'capital_105' => ['Capital deposited by owner', 'Bank Account', 'Owner Capital'],

            'withdraw_106' => ['Cash withdrawn by owner', 'Withdraw', 'Cash in Hand'],
            'withdraw_107' => ['Owner personal withdrawal', 'Withdraw', 'Cash in Hand'],
            'withdraw_108' => ['Owner withdrawal via bank', 'Withdraw', 'Bank Account'],
            'withdraw_109' => ['Drawing by owner', 'Withdraw', 'Cash in Hand'],
            'withdraw_110' => ['Owner cash withdrawal', 'Withdraw', 'Cash in Hand'],

            'retained_111' => ['Profit transferred to retained earnings', 'Sales Revenue', 'Retained Earnings'],
            'retained_112' => ['Year-end profit adjustment', 'Sales Revenue', 'Retained Earnings'],
            'retained_113' => ['Income transferred to reserves', 'Sales Revenue', 'Retained Earnings'],
            'retained_114' => ['Earnings retained in business', 'Sales Revenue', 'Retained Earnings'],
            'retained_115' => ['Net profit carried forward', 'Sales Revenue', 'Retained Earnings'],

            'adjustment_116' => ['Cash deposited into bank', 'Bank Account', 'Cash in Hand'],
            'adjustment_117' => ['Cash transferred to bank', 'Bank Account', 'Cash in Hand'],
            'adjustment_118' => ['Bank withdrawal to cash', 'Cash in Hand', 'Bank Account'],
            'adjustment_119' => ['Cash withdrawal from bank', 'Cash in Hand', 'Bank Account'],
            'adjustment_120' => ['Internal fund transfer', 'Bank Account', 'Cash in Hand'],
        ];

 

        $transactions = [];

        foreach ($rawTransactions as $key => $data) {
            [$description, $debitAccount, $creditAccount] = $data;

            $transactions[$key] = [
                'description'    => $description,
                'debit_account'  => $debitAccount,
                'credit_account' => $creditAccount,
            ];
        }

        return $transactions;

    }
}
