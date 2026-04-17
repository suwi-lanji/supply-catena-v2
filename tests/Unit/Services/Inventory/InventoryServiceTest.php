<?php

namespace Tests\Unit\Services\Inventory;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected InventoryService $service;
    protected Team $team;
    protected Item $item;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);

        $this->team = Team::factory()->create();

        $this->item = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Product',
            'opening_stock' => 100,
            'cost_price' => 50.00,
            'track_inventory_for_this_item' => true,
        ]);

        $this->warehouse = Warehouse::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Main Warehouse',
        ]);

        // Set initial warehouse stock
        DB::table('warehouse_items')->insert([
            'team_id' => $this->team->id,
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function it_can_increment_stock()
    {
        $initialStock = $this->item->stock_on_hand;

        $result = $this->service->incrementStock(
            $this->item,
            50,
            'purchase',
            null,
            'Stock received from supplier',
            $this->warehouse
        );

        $this->item->refresh();
        $this->assertEquals($initialStock + 50, $this->item->stock_on_hand);
    }

    /** @test */
    public function it_can_decrement_stock()
    {
        $initialStock = $this->item->stock_on_hand;

        $result = $this->service->decrementStock(
            $this->item,
            30,
            'sale',
            null,
            'Stock sold',
            $this->warehouse
        );

        $this->item->refresh();
        $this->assertEquals($initialStock - 30, $this->item->stock_on_hand);
    }

    /** @test */
    public function it_prevents_negative_stock()
    {
        $this->expectException(\Exception::class);

        // Try to decrement more than available
        $this->service->decrementStock(
            $this->item,
            200, // More than stock_on_hand (100)
            'sale',
            null,
            'Should fail',
            $this->warehouse
        );
    }

    /** @test */
    public function it_can_set_stock_to_specific_quantity()
    {
        $result = $this->service->setStock(
            $this->item,
            75,
            'Inventory count adjustment',
            $this->warehouse
        );

        $this->item->refresh();
        $this->assertEquals(75, $this->item->stock_on_hand);
    }

    /** @test */
    public function it_can_transfer_stock_between_warehouses()
    {
        $warehouse2 = Warehouse::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Secondary Warehouse',
        ]);

        // Initialize second warehouse stock
        DB::table('warehouse_items')->insert([
            'team_id' => $this->team->id,
            'item_id' => $this->item->id,
            'warehouse_id' => $warehouse2->id,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->service->transferStock(
            $this->item,
            $this->warehouse,
            $warehouse2,
            25,
            'Transfer order 001'
        );

        // Check source warehouse
        $sourceStock = $this->service->getWarehouseStock($this->item, $this->warehouse);
        $this->assertEquals(75, $sourceStock);

        // Check destination warehouse
        $destStock = $this->service->getWarehouseStock($this->item, $warehouse2);
        $this->assertEquals(25, $destStock);
    }

    /** @test */
    public function it_prevents_transfer_with_insufficient_stock()
    {
        $warehouse2 = Warehouse::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Secondary Warehouse',
        ]);

        $this->expectException(\Exception::class);

        // Try to transfer more than available
        $this->service->transferStock(
            $this->item,
            $this->warehouse,
            $warehouse2,
            150, // More than stock (100)
            'Should fail'
        );
    }

    /** @test */
    public function it_can_get_low_stock_items()
    {
        // Create items with different stock levels
        $lowStockItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Low Stock Product',
            'opening_stock' => 5,
            'reorder_level' => 10,
            'track_inventory_for_this_item' => true,
        ]);

        $normalStockItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Normal Stock Product',
            'opening_stock' => 50,
            'reorder_level' => 10,
            'track_inventory_for_this_item' => true,
        ]);

        $lowStockItems = $this->service->getLowStockItems($this->team);

        $this->assertTrue($lowStockItems->contains('id', $lowStockItem->id));
        $this->assertFalse($lowStockItems->contains('id', $normalStockItem->id));
    }

    /** @test */
    public function it_can_calculate_inventory_valuation()
    {
        // Create additional items
        $item2 = Item::factory()->create([
            'team_id' => $this->team->id,
            'opening_stock' => 50,
            'cost_price' => 30.00,
            'track_inventory_for_this_item' => true,
        ]);

        // Create warehouse items for the new item
        $warehouse2 = Warehouse::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Warehouse 2',
        ]);

        DB::table('warehouse_items')->insert([
            'team_id' => $this->team->id,
            'item_id' => $item2->id,
            'warehouse_id' => $warehouse2->id,
            'quantity' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $valuation = $this->service->getInventoryValuation($this->team);

        $this->assertArrayHasKey('total_value', $valuation);
        $this->assertArrayHasKey('total_quantity', $valuation);
        $this->assertArrayHasKey('item_count', $valuation);
        $this->assertArrayHasKey('items', $valuation);

        // Total value should be (100 * 50) + (50 * 30) = 5000 + 1500 = 6500
        $this->assertEquals(6500.00, $valuation['total_value']);
    }

    /** @test */
    public function it_logs_stock_movements()
    {
        $this->service->incrementStock(
            $this->item,
            25,
            'purchase',
            123,
            'Test movement',
            $this->warehouse
        );

        $movements = $this->service->getStockMovements($this->item);

        $this->assertGreaterThan(0, $movements->count());
        $lastMovement = $movements->first();
        $this->assertEquals('increment', $lastMovement->type);
        $this->assertEquals(25, $lastMovement->quantity);
        $this->assertEquals('purchase', $lastMovement->reference_type);
    }
}
