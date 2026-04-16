<?php

namespace Tests\Unit\Services\Accounting;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\LedgerAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\ChartOfAccountsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class JournalEntryServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected JournalEntryService $service;
    protected ChartOfAccountsService $chartOfAccountsService;
    protected Team $team;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(JournalEntryService::class);
        $this->chartOfAccountsService = app(ChartOfAccountsService::class);

        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();

        // Initialize chart of accounts for the team
        $this->chartOfAccountsService->initializeDefaultAccounts($this->team);
    }

    /** @test */
    public function it_can_create_a_journal_entry()
    {
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);

        $data = [
            'entry_date' => now(),
            'description' => 'Test entry',
            'user_id' => $this->user->id,
            'lines' => [
                [
                    'ledger_account_id' => $cashAccount->id,
                    'type' => 'debit',
                    'amount' => 100.00,
                ],
                [
                    'ledger_account_id' => $revenueAccount->id,
                    'type' => 'credit',
                    'amount' => 100.00,
                ],
            ],
        ];

        $entry = $this->service->create($this->team, $data);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals('draft', $entry->status);
        $this->assertEquals(2, $entry->lines()->count());
        $this->assertTrue($entry->isBalanced());
    }

    /** @test */
    public function it_can_create_a_simple_journal_entry()
    {
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);

        $entry = $this->service->createSimple(
            $this->team,
            $cashAccount->id,
            $revenueAccount->id,
            500.00,
            ['description' => 'Sales revenue']
        );

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals(500.00, $entry->getTotalDebits());
        $this->assertEquals(500.00, $entry->getTotalCredits());
    }

    /** @test */
    public function it_can_post_a_journal_entry()
    {
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);

        $entry = $this->service->createSimple(
            $this->team,
            $cashAccount->id,
            $revenueAccount->id,
            1000.00
        );

        $this->service->post($entry, $this->user->id);

        $this->assertEquals('posted', $entry->status);
        $this->assertNotNull($entry->posted_at);
        $this->assertEquals($this->user->id, $entry->posted_by);
    }

    /** @test */
    public function it_cannot_post_unbalanced_entry()
    {
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);

        $entry = JournalEntry::create([
            'team_id' => $this->team->id,
            'entry_date' => now(),
            'status' => 'draft',
        ]);

        // Create unbalanced lines
        $entry->lines()->create([
            'ledger_account_id' => $cashAccount->id,
            'type' => 'debit',
            'amount' => 100.00,
        ]);

        $entry->lines()->create([
            'ledger_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 50.00, // Different amount
        ]);

        $this->assertFalse($entry->canBePosted());

        $this->expectException(\Exception::class);
        $this->service->post($entry, $this->user->id);
    }

    /** @test */
    public function it_can_void_a_posted_entry()
    {
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);

        $entry = $this->service->createSimple(
            $this->team,
            $cashAccount->id,
            $revenueAccount->id,
            500.00
        );

        $this->service->post($entry, $this->user->id);
        $this->service->void($entry, $this->user->id, 'Test void');

        $this->assertEquals('voided', $entry->status);
        $this->assertNotNull($entry->voided_at);
        $this->assertEquals('Test void', $entry->void_reason);
    }

    /** @test */
    public function it_updates_account_balances_when_posted()
    {
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);

        $initialCashBalance = $cashAccount->current_balance;
        $initialRevenueBalance = $revenueAccount->current_balance;

        $entry = $this->service->createSimple(
            $this->team,
            $cashAccount->id,
            $revenueAccount->id,
            1000.00
        );

        $this->service->post($entry, $this->user->id);

        $cashAccount->refresh();
        $revenueAccount->refresh();

        // Cash is a debit account, so debit increases balance
        $this->assertEquals($initialCashBalance + 1000.00, $cashAccount->current_balance);

        // Revenue is a credit account, so credit increases balance
        $this->assertEquals($initialRevenueBalance + 1000.00, $revenueAccount->current_balance);
    }

    /** @test */
    public function it_can_generate_trial_balance()
    {
        // Create some entries
        $cashAccount = $this->chartOfAccountsService->getCash($this->team);
        $revenueAccount = $this->chartOfAccountsService->getSalesRevenue($this->team);
        $expenseAccount = LedgerAccount::where('team_id', $this->team->id)
            ->where('type', 'expense')
            ->first();

        // Revenue entry
        $entry1 = $this->service->createSimple(
            $this->team,
            $cashAccount->id,
            $revenueAccount->id,
            2000.00,
            ['user_id' => $this->user->id]
        );
        $this->service->post($entry1, $this->user->id);

        // Expense entry
        $entry2 = $this->service->createSimple(
            $this->team,
            $expenseAccount->id,
            $cashAccount->id,
            500.00,
            ['user_id' => $this->user->id]
        );
        $this->service->post($entry2, $this->user->id);

        $trialBalance = $this->service->getTrialBalance($this->team);

        $this->assertIsArray($trialBalance);
        $this->assertArrayHasKey('debit_total', $trialBalance);
        $this->assertArrayHasKey('credit_total', $trialBalance);
        $this->assertEquals(
            round($trialBalance['debit_total'], 2),
            round($trialBalance['credit_total'], 2)
        );
    }
}
