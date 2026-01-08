<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorksheetService
{
    public function getWorksheet($companyId, $date)
    {
        $asOfDate = Carbon::parse($date);

        $balances = JournalItem::whereHas('journalEntry', function($q) use ($asOfDate, $companyId) {
                $q->where('company_id', $companyId)
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

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->orderBy('code')
            ->get();

        $lines = [];
        $totals = [
            'tb_debit' => 0, 'tb_credit' => 0,
            'is_debit' => 0, 'is_credit' => 0,
            'bs_debit' => 0, 'bs_credit' => 0,
        ];

        foreach ($accounts as $account) {
            $balance = $balances->get($account->id);
            $debit = $balance->total_debit ?? 0;
            $credit = $balance->total_credit ?? 0;

            if ($debit == 0 && $credit == 0) continue;

            $net = $debit - $credit;
            $tb_dr = $net > 0 ? $net : 0;
            $tb_cr = $net < 0 ? abs($net) : 0;

            $line = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'type' => $account->type,
                'tb_debit' => $tb_dr,
                'tb_credit' => $tb_cr,
                'is_debit' => 0,
                'is_credit' => 0,
                'bs_debit' => 0,
                'bs_credit' => 0,
            ];

            if ($account->type === 'Income' || $account->type === 'Expense') {
                $line['is_debit'] = $tb_dr;
                $line['is_credit'] = $tb_cr;
                $totals['is_debit'] += $tb_dr;
                $totals['is_credit'] += $tb_cr;
            } else {
                $line['bs_debit'] = $tb_dr;
                $line['bs_credit'] = $tb_cr;
                $totals['bs_debit'] += $tb_dr;
                $totals['bs_credit'] += $tb_cr;
            }

            $totals['tb_debit'] += $tb_dr;
            $totals['tb_credit'] += $tb_cr;

            $lines[] = $line;
        }

        $netIncome = $totals['is_credit'] - $totals['is_debit'];

        return [
            'as_of' => $asOfDate->format('Y-m-d'),
            'lines' => $lines,
            'totals' => $totals,
            'net_income' => $netIncome,
        ];
    }
}
