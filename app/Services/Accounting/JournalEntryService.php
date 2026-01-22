<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Exception;

class JournalEntryService
{
    /**
     * Post a new journal entry with strict validation.
     * 
     * @param array $data
     * @return JournalEntry
     * @throws Exception
     */
    public function createEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            // 1. Validate Fiscal Year
            // In a real app, find the FY based on the date
            $fiscalYear = FiscalYear::where('company_id', $data['company_id'])
                ->where('start_date', '<=', $data['date'])
                ->where('end_date', '>=', $data['date'])
                ->first();

            if (!$fiscalYear) {
                throw new Exception("No open fiscal year found for date: " . $data['date']);
            }

            if ($fiscalYear->is_closed) {
                throw new Exception("The fiscal year is closed.");
            }

            // 2. Validate Double Entry Rule
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($data['items'] as $item) {
                $totalDebit += $item['debit'] ?? 0;
                $totalCredit += $item['credit'] ?? 0;
            }
            
            // Use epsilon for float comparison safety (though we use decimals strings usually)
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception("Journal Entry is not balanced. Debit: {$totalDebit}, Credit: {$totalCredit}");
            }

            // 3. Create Entry Header
            $entry = JournalEntry::create([
                'company_id' => $data['company_id'],
                'fiscal_year_id' => $fiscalYear->id,
                'entry_number' => $this->generateEntryNumber($data['company_id']),
                'date' => $data['date'],
                'description' => $data['description'],
                'reference' => $data['reference'] ?? null,
                'total_amount' => $totalDebit,
                'status' => 'posted', // Auto-post for now
            ]);

            // 4. Create Entry Items
            foreach ($data['items'] as $item) {
                $entry->items()->create([
                    'account_id' => $item['account_id'],
                    'description' => $item['description'] ?? null,
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                ]);
            }

            return $entry;
        });
    }

    public function createQuickEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $mainAccountId = $data['main_account_id'];
            $oppositeItemsInput = $data['opposite_items']; 
            $mainAccountItemsInput = $data['main_account_items'] ?? [];

            $items = [];
            $totalDebit = 0;
            $totalCredit = 0;
            $nullItems = [];

            // 1. Process Main Account Items
            if (empty($mainAccountItemsInput)) {
                $mainAccountItemsInput = [['description' => $data['main_account_description'] ?? $data['description'], 'amount' => null]];
            }

            foreach ($mainAccountItemsInput as $mItem) {
                $amount = isset($mItem['amount']) ? (float)$mItem['amount'] : null;
                $line = [
                    'account_id' => $mainAccountId,
                    'description' => $mItem['description'] ?? ($data['main_account_description'] ?? $data['description']),
                    'debit' => 0,
                    'credit' => 0,
                    'type' => $mItem['type'] ?? 'auto' // 'auto' means we'll decide DR/CR later
                ];

                if ($amount !== null && $amount > 0) {
                    $isDebit = ($mItem['type'] ?? 'debit') === 'debit';
                    $line['debit'] = $isDebit ? $amount : 0;
                    $line['credit'] = $isDebit ? 0 : $amount;
                    $totalDebit += $line['debit'];
                    $totalCredit += $line['credit'];
                    $items[] = $line;
                } else {
                    $nullItems[] = ['index' => count($items), 'side' => 'main', 'type' => $mItem['type'] ?? null];
                    $items[] = $line;
                }
            }

            // 2. Process Opposite Items
            foreach ($oppositeItemsInput as $oItem) {
                $amount = isset($oItem['amount']) ? (float)$oItem['amount'] : null;
                $line = [
                    'account_id' => $oItem['account_id'],
                    'description' => $oItem['description'] ?? $data['description'],
                    'debit' => 0,
                    'credit' => 0,
                    'type' => $oItem['type'] ?? 'auto'
                ];

                if ($amount !== null && $amount > 0) {
                    $isDebit = ($oItem['type'] ?? 'debit') === 'debit';
                    $line['debit'] = $isDebit ? $amount : 0;
                    $line['credit'] = $isDebit ? 0 : $amount;
                    $totalDebit += $line['debit'];
                    $totalCredit += $line['credit'];
                    $items[] = $line;
                } else {
                    $nullItems[] = ['index' => count($items), 'side' => 'opposite', 'type' => $oItem['type'] ?? null];
                    $items[] = $line;
                }
            }

            // 3. Balance the Entry
            $diff = $totalDebit - $totalCredit;
            if (count($nullItems) > 0) {
                // For now, support 1 null item for simplicity in balancing
                // If multiple null items, we distribute to the first one found
                $target = $nullItems[0];
                $absDiff = abs($diff);
                
                if ($diff > 0) {
                    // Need more credit
                    $items[$target['index']]['credit'] = $absDiff;
                } else {
                    // Need more debit
                    $items[$target['index']]['debit'] = $absDiff;
                }
            }

            // 4. Final check & format for createEntry
            $finalItems = array_map(function($item) {
                return [
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'],
                    'credit' => $item['credit'],
                    'description' => $item['description']
                ];
            }, $items);

            $entryData = [
                'company_id' => $data['company_id'],
                'date' => $data['date'],
                'description' => $data['description'],
                'items' => $finalItems,
                'reference' => $data['reference'] ?? null
            ];

            return $this->createEntry($entryData);
        });
    }

    private function generateEntryNumber($companyId): string
    {
        // Simple sequential number generator
        $count = JournalEntry::where('company_id', $companyId)->count();
        return 'JE-' . date('Y') . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
