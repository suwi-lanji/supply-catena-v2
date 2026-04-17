<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SalesOrdersResource;
use App\Filament\Resources\SalesOrdersResource\Pages\CreateSalesOrders;
use App\Filament\Resources\SalesOrdersResource\Pages\EditSalesOrders;
use App\Filament\Resources\SalesOrdersResource\Pages\ListSalesOrders;
use App\Filament\Resources\SalesOrdersResource\Pages\ViewSalesOrder;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesOrderResourceTest extends TestCase
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
        $this->get(SalesOrdersResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(SalesOrdersResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $salesOrder = $this->createSalesOrder();

        $this->get(SalesOrdersResource::getUrl('edit', ['record' => $salesOrder]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_view_page()
    {
        $salesOrder = $this->createSalesOrder();

        $this->get(SalesOrdersResource::getUrl('view', ['record' => $salesOrder]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_sales_orders()
    {
        $salesOrders = collect();
        for ($i = 0; $i < 5; $i++) {
            $salesOrders->push($this->createSalesOrder());
        }

        Livewire::test(ListSalesOrders::class)
            ->assertCanSeeTableRecords($salesOrders);
    }

    /** @test */
    public function it_can_create_a_sales_order()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'sales_order_number' => 'SO-2024-0001',
            'sales_order_date' => now()->format('Y-m-d'),
            'status' => 'confirmed',
            'items' => [],
            'sub_total' => 1000,
            'total' => 1000,
            'discount' => 0,
            'adjustment' => 0,
            'shipment_charges' => 0,
            'terms_and_conditions' => [],
        ];

        Livewire::test(CreateSalesOrders::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(SalesOrder::class, [
            'sales_order_number' => 'SO-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $salesOrder = $this->createSalesOrder();

        Livewire::test(EditSalesOrders::class, ['record' => $salesOrder->getRouteKey()])
            ->assertFormSet([
                'sales_order_number' => $salesOrder->sales_order_number,
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_view()
    {
        $salesOrder = $this->createSalesOrder();

        Livewire::test(ViewSalesOrder::class, ['record' => $salesOrder->getRouteKey()])
            ->assertFormSet([
                'sales_order_number' => $salesOrder->sales_order_number,
            ]);
    }

    /** @test */
    public function it_can_update_a_sales_order()
    {
        $salesOrder = $this->createSalesOrder();
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'sales_order_number' => 'SO-2024-UPDATED',
            'sales_order_date' => now()->format('Y-m-d'),
            'status' => 'delivered',
            'items' => [],
            'sub_total' => 2000,
            'total' => 2000,
            'discount' => 0,
            'adjustment' => 0,
            'shipment_charges' => 0,
            'terms_and_conditions' => [],
        ];

        Livewire::test(EditSalesOrders::class, ['record' => $salesOrder->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(SalesOrder::class, [
            'id' => $salesOrder->id,
            'sales_order_number' => 'SO-2024-UPDATED',
        ]);
    }

    protected function createSalesOrder(): SalesOrder
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        return SalesOrder::factory()->create([
            'team_id' => $this->team->id,
            'customer_id' => $customer->id,
        ]);
    }
}
