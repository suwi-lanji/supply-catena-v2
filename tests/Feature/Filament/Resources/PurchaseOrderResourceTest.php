<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\PurchaseOrderResource\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrderResource\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrderResource\Pages\ListPurchaseOrders;
use App\Filament\Resources\PurchaseOrderResource\Pages\ViewPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderResourceTest extends TestCase
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
        $this->get(PurchaseOrderResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(PurchaseOrderResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $purchaseOrder = $this->createPurchaseOrder();

        $this->get(PurchaseOrderResource::getUrl('edit', ['record' => $purchaseOrder]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_view_page()
    {
        $purchaseOrder = $this->createPurchaseOrder();

        $this->get(PurchaseOrderResource::getUrl('view', ['record' => $purchaseOrder]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_purchase_orders()
    {
        $purchaseOrders = collect();
        for ($i = 0; $i < 5; $i++) {
            $purchaseOrders->push($this->createPurchaseOrder());
        }

        Livewire::test(ListPurchaseOrders::class)
            ->assertCanSeeTableRecords($purchaseOrders);
    }

    /** @test */
    public function it_can_create_a_purchase_order()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'vendor_id' => $vendor->id,
            'purchase_order_number' => 'PO-2024-0001',
            'reference_number' => 'PO-2024-0001',
            'purchase_order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(14)->format('Y-m-d'),
            'order_status' => 'OPEN',
            'items' => [],
            'sub_total' => 1000,
            'total' => 1000,
            'discount' => 0,
            'adjustment' => 0,
            'delivery_street' => '123 Industrial Area',
            'delivery_city' => 'Kitwe',
            'delivery_province' => 'Copperbelt',
            'delivery_country' => 'Zambia',
            'delivery_phone' => '+260 123 456 789',
            'payment_terms' => 'Net 30',
            'shipment_preference' => 'Standard',
            'customer_notes' => '',
            'terms_and_conditions' => [],
            'received' => false,
            'billed' => false,
        ];

        Livewire::test(CreatePurchaseOrder::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(PurchaseOrder::class, [
            'purchase_order_number' => 'PO-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $purchaseOrder = $this->createPurchaseOrder();

        Livewire::test(EditPurchaseOrder::class, ['record' => $purchaseOrder->getRouteKey()])
            ->assertFormSet([
                'purchase_order_number' => $purchaseOrder->purchase_order_number,
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_view()
    {
        $purchaseOrder = $this->createPurchaseOrder();

        Livewire::test(ViewPurchaseOrder::class, ['record' => $purchaseOrder->getRouteKey()])
            ->assertFormSet([
                'purchase_order_number' => $purchaseOrder->purchase_order_number,
            ]);
    }

    /** @test */
    public function it_can_update_a_purchase_order()
    {
        $purchaseOrder = $this->createPurchaseOrder();
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'vendor_id' => $vendor->id,
            'purchase_order_number' => 'PO-2024-UPDATED',
            'reference_number' => 'PO-2024-UPDATED',
            'purchase_order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'order_status' => 'OPEN',
            'items' => [],
            'sub_total' => 2000,
            'total' => 2000,
            'discount' => 0,
            'adjustment' => 0,
            'delivery_street' => '456 Business Park',
            'delivery_city' => 'Lusaka',
            'delivery_province' => 'Lusaka',
            'delivery_country' => 'Zambia',
            'delivery_phone' => '+260 987 654 321',
            'payment_terms' => 'Net 30',
            'shipment_preference' => 'Express',
            'customer_notes' => 'Updated notes',
            'terms_and_conditions' => [],
            'received' => false,
            'billed' => false,
        ];

        Livewire::test(EditPurchaseOrder::class, ['record' => $purchaseOrder->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(PurchaseOrder::class, [
            'id' => $purchaseOrder->id,
            'purchase_order_number' => 'PO-2024-UPDATED',
        ]);
    }

    protected function createPurchaseOrder(): PurchaseOrder
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        return PurchaseOrder::factory()->create([
            'team_id' => $this->team->id,
            'vendor_id' => $vendor->id,
        ]);
    }
}
