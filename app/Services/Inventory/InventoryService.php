<?php

namespace App\Services\Inventory;

use App\Models\Item;
use App\Models\Warehouse;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class InventoryService extends BaseService
{
    /**
     * Increment stock for an item.
     *
     * @param Item $item
     * @param float $quantity
     * @param string $referenceType
     * @param int|null $referenceId
     * @param string $notes
     * @param Warehouse|null $warehouse
     * @return Item
     */
    public function incrementStock(
        Item $item,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        string $notes = '',
        ?Warehouse $warehouse = null
    ): Item {
        return $this->transaction(function () use ($item, $quantity, $referenceType, $referenceId, $notes, $warehouse) {
            // If warehouse is specified, update warehouse_items
            if ($warehouse) {
                $this->updateWarehouseStock($item, $warehouse, $quantity);
            } else {
                // Update opening_stock as fallback
                $item->opening_stock = ($item->opening_stock ?? 0) + $quantity;
                $item->save();
            }

            // Log the stock movement
            $this->logStockMovement($item, 'increment', $quantity, $referenceType, $referenceId, $notes);

            $this->logAction('stock_incremented', [
                'item_id' => $item->id,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            return $item;
        });
    }

    /**
     * Decrement stock for an item.
     *
     * @param Item $item
     * @param float $quantity
     * @param string $referenceType
     * @param int|null $referenceId
     * @param string $notes
     * @param Warehouse|null $warehouse
     * @return Item
     * @throws Exception
     */
    public function decrementStock(
        Item $item,
        float $quantity,
        string $referenceType,
        ?int $referenceId = null,
        string $notes = '',
        ?Warehouse $warehouse = null
    ): Item {
        return $this->transaction(function () use ($item, $quantity, $referenceType, $referenceId, $notes, $warehouse) {
            $currentStock = $item->stock_on_hand;

            if ($currentStock < $quantity && !$item->allow_negative_stock) {
                throw new Exception("Insufficient stock for item: {$item->name}. Available: {$currentStock}, Requested: {$quantity}");
            }

            // If warehouse is specified, update warehouse_items
            if ($warehouse) {
                $this->updateWarehouseStock($item, $warehouse, -$quantity);
            } else {
                // Update opening_stock as fallback
                $item->opening_stock = max(0, ($item->opening_stock ?? 0) - $quantity);
                $item->save();
            }

            // Log the stock movement
            $this->logStockMovement($item, 'decrement', $quantity, $referenceType, $referenceId, $notes);

            $this->logAction('stock_decremented', [
                'item_id' => $item->id,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            return $item;
        });
    }

    /**
     * Set stock to a specific quantity.
     *
     * @param Item $item
     * @param float $quantity
     * @param string $reason
     * @param Warehouse|null $warehouse
     * @return Item
     */
    public function setStock(Item $item, float $quantity, string $reason = '', ?Warehouse $warehouse = null): Item
    {
        return $this->transaction(function () use ($item, $quantity, $reason, $warehouse) {
            $oldQuantity = $item->stock_on_hand;
            $difference = $quantity - $oldQuantity;

            // If warehouse is specified, update warehouse_items
            if ($warehouse) {
                // Set exact quantity in warehouse
                DB::table('warehouse_items')
                    ->updateOrInsert(
                        [
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouse->id,
                        ],
                        [
                            'team_id' => $item->team_id,
                            'quantity' => $quantity,
                            'updated_at' => now(),
                        ]
                    );
            } else {
                // Update opening_stock as fallback
                $item->opening_stock = $quantity;
                $item->save();
            }

            // Log the adjustment
            $this->logStockMovement(
                $item,
                $difference > 0 ? 'adjustment_increase' : 'adjustment_decrease',
                abs($difference),
                'adjustment',
                null,
                $reason ?: "Stock adjusted from {$oldQuantity} to {$quantity}"
            );

            $this->logAction('stock_adjusted', [
                'item_id' => $item->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $quantity,
                'reason' => $reason,
            ]);

            return $item;
        });
    }

    /**
     * Transfer stock between warehouses.
     *
     * @param Item $item
     * @param Warehouse $fromWarehouse
     * @param Warehouse $toWarehouse
     * @param float $quantity
     * @param string $reference
     * @return void
     * @throws Exception
     */
    public function transferStock(
        Item $item,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        float $quantity,
        string $reference = ''
    ): void {
        $this->transaction(function () use ($item, $fromWarehouse, $toWarehouse, $quantity, $reference) {
            // Check source warehouse stock
            $sourceStock = $this->getWarehouseStock($item, $fromWarehouse);
            if ($sourceStock < $quantity) {
                throw new Exception("Insufficient stock in source warehouse.");
            }

            // Decrement from source warehouse
            $this->updateWarehouseStock($item, $fromWarehouse, -$quantity);

            // Increment in destination warehouse
            $this->updateWarehouseStock($item, $toWarehouse, $quantity);

            // Log the transfer
            $this->logStockMovement(
                $item,
                'transfer_out',
                $quantity,
                'warehouse_transfer',
                null,
                "Transferred to {$toWarehouse->name} - {$reference}"
            );

            $this->logStockMovement(
                $item,
                'transfer_in',
                $quantity,
                'warehouse_transfer',
                null,
                "Transferred from {$fromWarehouse->name} - {$reference}"
            );

            $this->logAction('stock_transferred', [
                'item_id' => $item->id,
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $toWarehouse->id,
                'quantity' => $quantity,
            ]);
        });
    }

    /**
     * Get stock quantity in a warehouse.
     *
     * @param Item $item
     * @param Warehouse $warehouse
     * @return float
     */
    public function getWarehouseStock(Item $item, Warehouse $warehouse): float
    {
        $warehouseItem = DB::table('warehouse_items')
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        return $warehouseItem?->quantity ?? 0;
    }

    /**
     * Update stock in a specific warehouse.
     *
     * @param Item $item
     * @param Warehouse $warehouse
     * @param float $quantityChange
     * @return void
     */
    protected function updateWarehouseStock(Item $item, Warehouse $warehouse, float $quantityChange): void
    {
        DB::table('warehouse_items')
            ->updateOrInsert(
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'team_id' => $item->team_id,
                    'quantity' => DB::raw("COALESCE(quantity, 0) + {$quantityChange}"),
                    'updated_at' => now(),
                ]
            );
    }

    /**
     * Log a stock movement.
     *
     * @param Item $item
     * @param string $type
     * @param float $quantity
     * @param string $referenceType
     * @param int|null $referenceId
     * @param string $notes
     * @return void
     */
    protected function logStockMovement(
        Item $item,
        string $type,
        float $quantity,
        string $referenceType,
        ?int $referenceId,
        string $notes
    ): void {
        DB::table('stock_movements')->insert([
            'team_id' => $item->team_id,
            'item_id' => $item->id,
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Get items with low stock.
     *
     * @param Team $team
     * @param float|null $threshold
     * @return Collection
     */
    public function getLowStockItems(Team $team, ?float $threshold = null): Collection
    {
        $query = Item::where('team_id', $team->id)
            ->where('track_inventory_for_this_item', true);

        if ($threshold !== null) {
            // Use opening_stock for comparison since stock_on_hand is computed
            $query->where('opening_stock', '<=', $threshold);
        } else {
            $query->whereColumn('opening_stock', '<=', 'reorder_level');
        }

        return $query->get();
    }

    /**
     * Get inventory valuation for a team.
     *
     * @param Team $team
     * @return array
     */
    public function getInventoryValuation(Team $team): array
    {
        $items = Item::where('team_id', $team->id)
            ->where('track_inventory_for_this_item', true)
            ->get();

        $totalValue = 0;
        $totalQuantity = 0;
        $itemValues = [];

        foreach ($items as $item) {
            $stockOnHand = $item->stock_on_hand;
            $value = $stockOnHand * $item->cost_price;
            $totalValue += $value;
            $totalQuantity += $stockOnHand;

            $itemValues[] = [
                'item' => $item,
                'quantity' => $stockOnHand,
                'unit_cost' => $item->cost_price,
                'total_value' => $value,
            ];
        }

        return [
            'total_value' => $totalValue,
            'total_quantity' => $totalQuantity,
            'item_count' => $items->count(),
            'items' => $itemValues,
        ];
    }

    /**
     * Get stock movements for an item.
     *
     * @param Item $item
     * @param int $limit
     * @return Collection
     */
    public function getStockMovements(Item $item, int $limit = 50): Collection
    {
        return collect(DB::table('stock_movements')
            ->where('item_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get());
    }
}
