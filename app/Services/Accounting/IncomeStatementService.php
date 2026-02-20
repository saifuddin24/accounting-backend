<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeStatementService
{
    public function getIncomeStatement($profileId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $balances = JournalItem::whereHas('journalEntry', function ($q) use ($start, $end, $profileId) {
            $q->where('profile_id', $profileId)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
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
            ->whereIn('type', ['Income', 'Expense'])
            ->orderBy('code')
            ->get();

        $revenues = [];
        $expenses = [];
        $totalRevenues = 0;
        $totalExpenses = 0;

        foreach ($accounts as $account) {
            $balance = $balances->get($account->id);
            if (!$balance) continue;

            $debit = $balance->total_debit;
            $credit = $balance->total_credit;

            if ($account->type === 'Income') {
                $amount = $credit - $debit;
                if ($amount == 0) continue;
                $revenues[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $amount
                ];
                $totalRevenues += $amount;
            } else {
                $amount = $debit - $credit;
                if ($amount == 0) continue;
                $expenses[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'amount' => $amount
                ];
                $totalExpenses += $amount;
            }
        }

        return [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'revenues' => $revenues,
            'total_revenues' => $totalRevenues,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_income' => $totalRevenues - $totalExpenses
        ];
    }
}
