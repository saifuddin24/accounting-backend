<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function getTrialBalance($profileId, $asOfDate = null)
    {
        $date = $asOfDate ? Carbon::parse($asOfDate) : Carbon::now();

        // 1. Get Balances grouped by Account
        // We sum all posted debits and credits up to the date
        $balances = JournalItem::whereHas('journalEntry', function ($q) use ($date, $profileId) {
            $q->where('profile_id', $profileId)
                ->where('date', '<=', $date)
                ->where('status', 'posted');
        })
            ->select(
                'account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // 2. Fetch all Accounts to show even those with 0 balance (optional, but good practice)
        // or just show those with activity. Let's show all active accounts.
        $accounts = ChartOfAccount::where('profile_id', $profileId)
            ->orderBy('code')
            ->get();

        $reportData = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $balances->get($account->id);
            $debitSum = $balance->total_debit ?? 0;
            $creditSum = $balance->total_credit ?? 0;

            $net = $debitSum - $creditSum;

            // Logic:
            // If Net is Positive, it's a Debit Balance
            // If Net is Negative, it's a Credit Balance

            $rowDebit = 0;
            $rowCredit = 0;

            if ($net > 0) {
                $rowDebit = $net;
            } elseif ($net < 0) {
                $rowCredit = abs($net);
            }

            if ($rowDebit == 0 && $rowCredit == 0) continue; // Skip zero balance accounts

            $reportData[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'type' => $account->type,
                'debit' => $rowDebit,
                'credit' => $rowCredit
            ];

            $totalDebit += $rowDebit;
            $totalCredit += $rowCredit;
        }

        return [
            'as_of' => $date->format('Y-m-d'),
            'lines' => $reportData,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01
        ];
    }
}
