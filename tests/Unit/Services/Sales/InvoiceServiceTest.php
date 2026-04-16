<?php

namespace Tests\Unit\Services\Sales;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Invoices;
use App\Models\LedgerAccount;
use App\Services\Sales\InvoiceService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\ChartOfAccountsService;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected InvoiceService $service;
    protected Team $team;
    protected User $user;
    protected Customer $customer;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InvoiceService::class);

        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();

        // Initialize chart of accounts
        app(ChartOfAccountsService::class)->initializeDefaultAccounts($this->team);

        // Create test customer
        $this->customer = Customer::factory()->create([
            'team_id' => $this->team->id,
            'company_name' => 'Test Customer Company',
        ]);

        // Create test item with opening stock
        $this->item = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Product',
            'selling_price' => 100.00,
            'cost_price' => 60.00,
            'track_inventory_for_this_item' => true,
            'opening_stock' => 100,
        ]);
    }

    /** @test */
    public function it_can_create_an_invoice()
    {
        $data = [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'description' => 'Test Item',
                    'quantity' => 2,
                    'rate' => 100.00,
                    'tax_rate' => 16,
                ],
            ],
        ];

        $invoice = $this->service->create($this->team, $data);

        $this->assertInstanceOf(Invoices::class, $invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(200.00, $invoice->subtotal);
        $this->assertEquals(32.00, $invoice->tax); // 16% of 200
        $this->assertEquals(232.00, $invoice->total);
    }

    /** @test */
    public function it_can_send_an_invoice()
    {
        $invoice = $this->service->create($this->team, [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'description' => 'Test Item',
                    'quantity' => 5,
                    'rate' => 100.00,
                ],
            ],
        ]);

        $initialStock = $this->item->stock_on_hand;

        $this->service->send($invoice, $this->user->id);

        $this->assertEquals('sent', $invoice->status);
        $this->assertEquals($invoice->total, $invoice->balance_due);

        // Check stock was decremented
        $this->item->refresh();
        $this->assertEquals($initialStock - 5, $this->item->stock_on_hand);
    }

    /** @test */
    public function it_can_apply_payment_to_invoice()
    {
        $invoice = $this->service->create($this->team, [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'rate' => 100.00,
                ],
            ],
        ]);

        $this->service->send($invoice, $this->user->id);

        // Apply partial payment
        $this->service->applyPayment($invoice, 50.00, 1);

        $invoice->refresh();
        $this->assertEquals(50.00, $invoice->balance_due);
        $this->assertEquals('partial', $invoice->status);

        // Apply remaining payment
        $this->service->applyPayment($invoice, 50.00, 2);

        $invoice->refresh();
        $this->assertEquals(0, $invoice->balance_due);
        $this->assertEquals('paid', $invoice->status);
    }

    /** @test */
    public function it_can_cancel_a_draft_invoice()
    {
        $invoice = $this->service->create($this->team, [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'rate' => 100.00,
                ],
            ],
        ]);

        $this->service->cancel($invoice, $this->user->id, 'Customer request');

        $this->assertEquals('cancelled', $invoice->status);
    }

    /** @test */
    public function it_cannot_update_sent_invoice()
    {
        $invoice = $this->service->create($this->team, [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'rate' => 100.00,
                ],
            ],
        ]);

        $this->service->send($invoice, $this->user->id);

        $this->expectException(\Exception::class);
        $this->service->update($invoice, [
            'items' => [
                [
                    'description' => 'Modified Item',
                    'quantity' => 2,
                    'rate' => 200.00,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_calculates_totals_correctly()
    {
        $data = [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'items' => [
                [
                    'description' => 'Item 1',
                    'quantity' => 2,
                    'rate' => 100.00,
                    'tax_rate' => 16,
                ],
                [
                    'description' => 'Item 2',
                    'quantity' => 3,
                    'rate' => 50.00,
                    'tax_rate' => 16,
                ],
            ],
        ];

        $invoice = $this->service->create($this->team, $data);

        // Subtotal: (2 * 100) + (3 * 50) = 350
        $this->assertEquals(350.00, $invoice->subtotal);

        // Tax: 16% of 350 = 56
        $this->assertEquals(56.00, $invoice->tax);

        // Total: 350 + 56 = 406
        $this->assertEquals(406.00, $invoice->total);
    }

    /** @test */
    public function it_creates_journal_entry_when_sent()
    {
        $invoice = $this->service->create($this->team, [
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'rate' => 100.00,
                ],
            ],
        ]);

        $this->service->send($invoice, $this->user->id);

        // Check journal entry was created
        $journalEntry = $invoice->journalEntries()->first();

        $this->assertNotNull($journalEntry);
        $this->assertEquals('posted', $journalEntry->status);
        $this->assertTrue($journalEntry->isBalanced());
    }
}
