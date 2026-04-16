<?php

namespace Tests\Unit\Services\Accounting;

use Tests\TestCase;
use App\Models\Team;
use App\Models\LedgerAccount;
use App\Services\Accounting\ChartOfAccountsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ChartOfAccountsServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ChartOfAccountsService $service;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ChartOfAccountsService::class);
        $this->team = Team::factory()->create();
    }

    /** @test */
    public function it_can_create_a_ledger_account()
    {
        $data = [
            'code' => '1001',
            'name' => 'Test Cash Account',
            'type' => LedgerAccount::TYPE_ASSET,
            'sub_type' => LedgerAccount::SUB_TYPE_CASH,
            'description' => 'Test account for cash',
        ];

        $account = $this->service->createAccount($this->team, $data);

        $this->assertInstanceOf(LedgerAccount::class, $account);
        $this->assertEquals('1001', $account->code);
        $this->assertEquals('Test Cash Account', $account->name);
        $this->assertEquals(LedgerAccount::TYPE_ASSET, $account->type);
        $this->assertTrue($account->is_active);
    }

    /** @test */
    public function it_initializes_default_chart_of_accounts()
    {
        $accounts = $this->service->initializeDefaultAccounts($this->team);

        $this->assertGreaterThan(0, $accounts->count());

        // Check for essential accounts
        $this->assertNotNull($this->service->getCash($this->team));
        $this->assertNotNull($this->service->getAccountsReceivable($this->team));
        $this->assertNotNull($this->service->getAccountsPayable($this->team));
        $this->assertNotNull($this->service->getSalesRevenue($this->team));
        $this->assertNotNull($this->service->getInventory($this->team));
    }

    /** @test */
    public function it_can_get_accounts_by_type()
    {
        // Create some accounts
        $this->service->createAccount($this->team, [
            'code' => '1000',
            'name' => 'Asset Account',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);

        $this->service->createAccount($this->team, [
            'code' => '2000',
            'name' => 'Liability Account',
            'type' => LedgerAccount::TYPE_LIABILITY,
        ]);

        $assets = $this->service->getAccountsByType($this->team, LedgerAccount::TYPE_ASSET);
        $liabilities = $this->service->getAccountsByType($this->team, LedgerAccount::TYPE_LIABILITY);

        $this->assertEquals(1, $assets->count());
        $this->assertEquals(1, $liabilities->count());
    }

    /** @test */
    public function it_validates_account_data()
    {
        $this->expectException(\Exception::class);

        // Missing required fields
        $this->service->createAccount($this->team, [
            'type' => LedgerAccount::TYPE_ASSET,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_account_codes()
    {
        $this->service->createAccount($this->team, [
            'code' => '1000',
            'name' => 'First Account',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);

        $this->expectException(\Exception::class);

        $this->service->createAccount($this->team, [
            'code' => '1000', // Duplicate code
            'name' => 'Second Account',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);
    }

    /** @test */
    public function it_can_update_an_account()
    {
        $account = $this->service->createAccount($this->team, [
            'code' => '1000',
            'name' => 'Original Name',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);

        $updated = $this->service->updateAccount($account, [
            'name' => 'Updated Name',
            'description' => 'New description',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('New description', $updated->description);
    }

    /** @test */
    public function it_can_delete_an_account_without_transactions()
    {
        $account = $this->service->createAccount($this->team, [
            'code' => '9999',
            'name' => 'Deletable Account',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);

        $result = $this->service->deleteAccount($account);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ledger_accounts', ['id' => $account->id]);
    }

    /** @test */
    public function it_identifies_debit_and_credit_accounts_correctly()
    {
        $assetAccount = $this->service->createAccount($this->team, [
            'code' => '1000',
            'name' => 'Asset',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);

        $liabilityAccount = $this->service->createAccount($this->team, [
            'code' => '2000',
            'name' => 'Liability',
            'type' => LedgerAccount::TYPE_LIABILITY,
        ]);

        $this->assertTrue($assetAccount->isDebitAccount());
        $this->assertFalse($assetAccount->isCreditAccount());

        $this->assertTrue($liabilityAccount->isCreditAccount());
        $this->assertFalse($liabilityAccount->isDebitAccount());
    }

    /** @test */
    public function it_returns_correct_normal_balance()
    {
        $assetAccount = $this->service->createAccount($this->team, [
            'code' => '1000',
            'name' => 'Asset',
            'type' => LedgerAccount::TYPE_ASSET,
        ]);

        $revenueAccount = $this->service->createAccount($this->team, [
            'code' => '4000',
            'name' => 'Revenue',
            'type' => LedgerAccount::TYPE_REVENUE,
        ]);

        $this->assertEquals('debit', $assetAccount->getNormalBalance());
        $this->assertEquals('credit', $revenueAccount->getNormalBalance());
    }
}
