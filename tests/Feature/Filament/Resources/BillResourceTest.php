<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\BillResource;
use App\Filament\Resources\BillResource\Pages\CreateBill;
use App\Filament\Resources\BillResource\Pages\EditBill;
use App\Filament\Resources\BillResource\Pages\ListBills;
use App\Filament\Resources\BillResource\Pages\ViewBillResource;
use App\Models\Bill;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\PaymentTerm;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillResourceTest extends TestCase
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
        $this->get(BillResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(BillResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $bill = $this->createBill();

        $this->get(BillResource::getUrl('edit', ['record' => $bill]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_view_page()
    {
        $bill = $this->createBill();

        $this->get(BillResource::getUrl('view', ['record' => $bill]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_bills()
    {
        $bills = collect();
        for ($i = 0; $i < 5; $i++) {
            $bills->push($this->createBill());
        }

        Livewire::test(ListBills::class)
            ->assertCanSeeTableRecords($bills);
    }

    /** @test */
    public function it_can_create_a_bill()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);
        $purchaseOrder = $this->createPurchaseOrder($vendor);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'vendor_id' => $vendor->id,
            'bill_number' => 'BL-2024-0001',
            'order_number' => $purchaseOrder->id,
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_terms' => $paymentTerm->id,
            'items' => [],
            'sub_total' => 1000,
            'total' => 1000,
            'balance_due' => 1000,
            'discount' => 0,
            'adjustment' => 0,
        ];

        Livewire::test(CreateBill::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Bill::class, [
            'bill_number' => 'BL-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $bill = $this->createBill();

        Livewire::test(EditBill::class, ['record' => $bill->getRouteKey()])
            ->assertFormSet([
                'bill_number' => $bill->bill_number,
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_view()
    {
        $bill = $this->createBill();

        Livewire::test(ViewBillResource::class, ['record' => $bill->getRouteKey()])
            ->assertFormSet([
                'bill_number' => $bill->bill_number,
            ]);
    }

    /** @test */
    public function it_can_update_a_bill()
    {
        $bill = $this->createBill();
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);
        $purchaseOrder = $this->createPurchaseOrder($vendor);

        $newData = [
            'vendor_id' => $vendor->id,
            'bill_number' => 'BL-2024-UPDATED',
            'order_number' => $purchaseOrder->id,
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'items' => [],
            'sub_total' => 2000,
            'total' => 2000,
            'balance_due' => 2000,
            'discount' => 0,
            'adjustment' => 0,
        ];

        Livewire::test(EditBill::class, ['record' => $bill->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Bill::class, [
            'id' => $bill->id,
            'bill_number' => 'BL-2024-UPDATED',
        ]);
    }

    protected function createBill(): Bill
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);
        $purchaseOrder = $this->createPurchaseOrder($vendor);

        return Bill::factory()->create([
            'team_id' => $this->team->id,
            'vendor_id' => $vendor->id,
            'order_number' => $purchaseOrder->id,
        ]);
    }

    protected function createPurchaseOrder($vendor): PurchaseOrder
    {
        return PurchaseOrder::factory()->create([
            'team_id' => $this->team->id,
            'vendor_id' => $vendor->id,
        ]);
    }
}
