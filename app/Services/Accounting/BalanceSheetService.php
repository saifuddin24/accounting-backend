<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    public function getBalanceSheet($profileId, $date)
    {
        $asOfDate = Carbon::parse($date);

        // 1. Get all balances up to $asOfDate
        $balances = JournalItem::whereHas('journalEntry', function ($q) use ($asOfDate, $profileId) {
            $q->where('profile_id', $profileId)
                ->where('date', '<=', $asOfDate->format('Y-m-d'))
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

        $accounts = ChartOfAccount::where('profile_id', $profileId)
            ->orderBy('code')
            ->get();

        $assets = [];
        $liabilities = [];
        $equity = [];

        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;
        $totalRestrictedAssets = 0;
        $totalGeneralAssets = 0;
        $netIncome = 0;

        foreach ($accounts as $account) {
            $balance = $balances->get($account->id);
            $debit = $balance->total_debit ?? 0;
            $credit = $balance->total_credit ?? 0;

            if ($account->type === 'Asset') {
                $amount = $debit - $credit;
                $restrictedAmount = $account->is_restricted ? $amount : 0;

                if ($amount != 0) {
                    $assets[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'amount' => $amount,
                        'is_restricted' => $account->is_restricted,
                        'restricted_amount' => $restrictedAmount
                    ];
                    $totalAssets += $amount;

                    if ($account->is_restricted) {
                        $totalRestrictedAssets += $amount;
                    } else {
                        $totalGeneralAssets += $amount;
                    }
                }
            } elseif ($account->type === 'Liability') {
                $amount = $credit - $debit;
                if ($amount != 0) {
                    $liabilities[] = ['account_code' => $account->code, 'account_name' => $account->name, 'amount' => $amount];
                    $totalLiabilities += $amount;
                }
            } elseif ($account->type === 'Equity') {
                $amount = $credit - $debit;
                if ($amount != 0) {
                    $equity[] = ['account_code' => $account->code, 'account_name' => $account->name, 'amount' => $amount];
                    $totalEquity += $amount;
                }
            } elseif ($account->type === 'Income') {
                $netIncome += ($credit - $debit);
            } elseif ($account->type === 'Expense') {
                $netIncome -= ($debit - $credit);
            }
        }

        // Add Net Income to Equity
        if ($netIncome != 0) {
            $equity[] = [
                'account_code' => 'NET_INCOME',
                'account_name' => 'Net Income (Retained Earnings)',
                'amount' => $netIncome
            ];
            $totalEquity += $netIncome;
        }

        return [
            'as_of' => $asOfDate->format('Y-m-d'),
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'total_restricted_assets' => $totalRestrictedAssets,
            'total_general_assets' => $totalGeneralAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01
        ];
    }
}
