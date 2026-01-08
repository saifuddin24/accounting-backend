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

    private function generateEntryNumber($companyId): string
    {
        // Simple sequential number generator
        $count = JournalEntry::where('company_id', $companyId)->count();
        return 'JE-' . date('Y') . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
