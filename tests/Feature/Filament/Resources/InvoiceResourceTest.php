<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\InvoicesResource;
use App\Filament\Resources\InvoicesResource\Pages\CreateInvoices;
use App\Filament\Resources\InvoicesResource\Pages\EditInvoices;
use App\Filament\Resources\InvoicesResource\Pages\ListInvoices;
use App\Filament\Resources\InvoicesResource\Pages\ViewInvoice;
use App\Models\Invoices;
use App\Models\Customer;
use App\Models\PaymentTerm;
use App\Models\SalesOrder;
use App\Models\SalesPerson;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->team = Team::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        Filament::setTenant($this->team);
    }

    /** @test */
    public function it_can_render_the_list_page()
    {
        $this->get(InvoicesResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(InvoicesResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $invoice = $this->createInvoice();

        $this->get(InvoicesResource::getUrl('edit', ['record' => $invoice]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_view_page()
    {
        $invoice = $this->createInvoice();

        $this->get(InvoicesResource::getUrl('view', ['record' => $invoice]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_invoices()
    {
        $invoices = collect();
        for ($i = 0; $i < 5; $i++) {
            $invoices->push($this->createInvoice());
        }

        Livewire::test(ListInvoices::class)
            ->assertCanSeeTableRecords($invoices);
    }

    /** @test */
    public function it_can_create_an_invoice()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);
        $salesOrder = $this->createSalesOrder($customer);
        $salesPerson = SalesPerson::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2024-0001',
            'type' => 'tax',
            'order_number' => $salesOrder->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_terms_id' => $paymentTerm->id,
            'sales_person_id' => $salesPerson->id,
            'items' => [],
            'sub_total' => 1000,
            'total' => 1000,
            'balance_due' => 1000,
            'discount' => 0,
            'adjustment' => 0,
        ];

        Livewire::test(CreateInvoices::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Invoices::class, [
            'invoice_number' => 'INV-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $invoice = $this->createInvoice();

        Livewire::test(EditInvoices::class, ['record' => $invoice->getRouteKey()])
            ->assertFormSet([
                'invoice_number' => $invoice->invoice_number,
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_view()
    {
        $invoice = $this->createInvoice();

        Livewire::test(ViewInvoice::class, ['record' => $invoice->getRouteKey()])
            ->assertFormSet([
                'invoice_number' => $invoice->invoice_number,
            ]);
    }

    /** @test */
    public function it_can_update_an_invoice()
    {
        $invoice = $this->createInvoice();
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);
        $salesOrder = $this->createSalesOrder($customer);
        $salesPerson = SalesPerson::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2024-UPDATED',
            'type' => 'tax',
            'order_number' => $salesOrder->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'payment_terms_id' => $paymentTerm->id,
            'sales_person_id' => $salesPerson->id,
            'items' => [],
            'sub_total' => 2000,
            'total' => 2000,
            'balance_due' => 2000,
            'discount' => 0,
            'adjustment' => 0,
        ];

        Livewire::test(EditInvoices::class, ['record' => $invoice->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Invoices::class, [
            'id' => $invoice->id,
            'invoice_number' => 'INV-2024-UPDATED',
        ]);
    }

    /** @test */
    public function it_can_delete_an_invoice()
    {
        $invoice = $this->createInvoice();

        Livewire::test(EditInvoices::class, ['record' => $invoice->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($invoice);
    }

    protected function createInvoice(): Invoices
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);
        $salesOrder = $this->createSalesOrder($customer);
        $salesPerson = SalesPerson::factory()->create(['team_id' => $this->team->id]);

        return Invoices::factory()->create([
            'team_id' => $this->team->id,
            'customer_id' => $customer->id,
            'payment_terms_id' => $paymentTerm->id,
            'order_number' => $salesOrder->id,
            'sales_person_id' => $salesPerson->id,
        ]);
    }

    protected function createSalesOrder($customer): SalesOrder
    {
        return SalesOrder::factory()->create([
            'team_id' => $this->team->id,
            'customer_id' => $customer->id,
        ]);
    }
}
