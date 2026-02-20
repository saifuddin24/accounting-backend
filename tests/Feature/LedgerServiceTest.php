<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use App\Models\Contact; // Assuming Contact model exists
use App\Services\Accounting\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_get_ledger_filters_by_contact_id()
  {
    // Setup
    $ledgerService = new LedgerService();
    $account = ChartOfAccount::factory()->create(['name' => 'Cash', 'code' => '1000', 'type' => 'asset']);
    $contact1 = \DB::table('contacts')->insertGetId(['name' => 'Contact 1', 'type' => 'customer']);
    $contact2 = \DB::table('contacts')->insertGetId(['name' => 'Contact 2', 'type' => 'customer']);

    // Create Journal Entry with Contact 1
    $entry1 = JournalEntry::create([
      'date' => '2023-01-01',
      'contact_id' => $contact1,
      'description' => 'Entry 1',
      'entry_number' => 'JE-001',
      'status' => 'posted',
    ]);
    JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $account->id, 'debit' => 100, 'credit' => 0]);
    JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $account->id, 'debit' => 0, 'credit' => 100]); // Balanced entry for simplicity self-balancing

    // Create Journal Entry with Contact 2
    $entry2 = JournalEntry::create([
      'date' => '2023-01-02',
      'contact_id' => $contact2,
      'description' => 'Entry 2',
      'entry_number' => 'JE-002',
      'status' => 'posted',
    ]);
    JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $account->id, 'debit' => 200, 'credit' => 0]);
    JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $account->id, 'debit' => 0, 'credit' => 200]);

    // Create Journal Entry without Contact
    $entry3 = JournalEntry::create([
      'date' => '2023-01-03',
      // contact_id is null
      'description' => 'Entry 3',
      'entry_number' => 'JE-003',
      'status' => 'posted',
    ]);
    JournalItem::create(['journal_entry_id' => $entry3->id, 'account_id' => $account->id, 'debit' => 300, 'credit' => 0]);
    JournalItem::create(['journal_entry_id' => $entry3->id, 'account_id' => $account->id, 'debit' => 0, 'credit' => 300]);


    // Test filtering by Contact 1
    $result1 = $ledgerService->getLedger($account->id, '2023-01-01', '2023-12-31', $contact1);
    $this->assertEquals(1, count($result1['lines']));
    $this->assertEquals('Entry 1', $result1['lines'][0]['description']);

    // Test filtering by Contact 2
    $result2 = $ledgerService->getLedger($account->id, '2023-01-01', '2023-12-31', $contact2);
    $this->assertEquals(1, count($result2['lines']));
    $this->assertEquals('Entry 2', $result2['lines'][0]['description']);

    // Test without filtering
    $resultAll = $ledgerService->getLedger($account->id, '2023-01-01', '2023-12-31');
    $this->assertGreaterThanOrEqual(3, count($resultAll['lines']));
  }
}
