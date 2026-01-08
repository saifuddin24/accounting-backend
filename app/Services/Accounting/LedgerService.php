<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Carbon\Carbon;

class LedgerService
{
    /**
     * Get Ledger for a specific account.
     */
    public function getLedger($accountId, $startDate = null, $endDate = null)
    {
        $account = ChartOfAccount::findOrFail($accountId);
        
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfYear();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfYear();

        // 1. Calculate Opening Balance (Sum of all previous transactions)
        $openingBalanceQuery = JournalItem::where('account_id', $accountId)
            ->whereHas('journalEntry', function($q) use ($startDate) {
                $q->where('date', '<', $startDate->format('Y-m-d'))
                  ->where('status', 'posted');
            });

        $openingDebit = $openingBalanceQuery->sum('debit');
        $openingCredit = $openingBalanceQuery->sum('credit');
        
        $netOpening = $this->calculateNetBalance($account, $openingDebit, $openingCredit);

        // 2. Fetch Transactions in the period
        $transactions = JournalItem::with(['journalEntry.items.account'])
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', 'posted');
            })
            // Important: Order by Date then Entry ID then Item ID to ensure consistent running balance
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.date')
            ->orderBy('journal_entries.id')
            ->orderBy('journal_items.id')
            ->select('journal_items.*') // Avoid column collisions
            ->get();

        // 3. Calculate Running Balance
        $runningBalance = $netOpening;
        $ledgerLines = [];

        foreach ($transactions as $item) {
            $debit = (float) $item->debit;
            $credit = (float) $item->credit;
            
            // Calculate movement based on Account Normal Balance
            if ($account->normal_balance === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            // Find opposite account(s)
            $isDebit = $debit > 0;
            $oppositeAccounts = $item->journalEntry->items->filter(function($oi) use ($isDebit, $item) {
                // If this is a debit line, opposite is credit lines and vice versa
                // If it's a multi-line entry, we want the "other side"
                return $isDebit ? ($oi->credit > 0) : ($oi->debit > 0);
            });

            // If no clear "other side" (e.g. debit to debit adjustments), show other accounts in entry
            if ($oppositeAccounts->isEmpty()) {
                $oppositeAccounts = $item->journalEntry->items->where('account_id', '!=', $item->account_id);
            }

            $oppositeAccountName = $oppositeAccounts->map(function($oa) {
                return $oa->account->name;
            })->unique()->implode(', ');

            if (empty($oppositeAccountName)) {
                $oppositeAccountName = 'N/A';
            }

            $ledgerLines[] = [
                'id' => $item->id,
                'date' => $item->journalEntry->date->format('Y-m-d'),
                'entry_number' => $item->journalEntry->entry_number,
                'opposite_account' => $oppositeAccountName,
                'description' => $item->description ?? $item->journalEntry->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance
            ];
        }

        return [
            'account' => $account,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'opening_balance' => $netOpening,
            'lines' => $ledgerLines,
            'closing_balance' => $runningBalance
        ];
    }

    private function calculateNetBalance($account, $debit, $credit)
    {
        if ($account->normal_balance === 'debit') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }
}
