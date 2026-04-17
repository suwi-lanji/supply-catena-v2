<?php

namespace App\Services\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\Item;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Exception;

class InventoryAdjustmentService extends BaseService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new inventory adjustment.
     *
     * @param Team $team
     * @param array $data
     * @return InventoryAdjustment
     * @throws Exception
     */
    public function create(Team $team, array $data): InventoryAdjustment
    {
        return $this->transaction(function () use ($team, $data) {
            $adjustment = new InventoryAdjustment();
            $adjustment->team_id = $team->id;
            $adjustment->mode_of_adjustment = $data['mode_of_adjustment'] ?? 'Quantity';
            $adjustment->reference_number = $data['reference_number'] ?? $this->generateReferenceNumber($team);
            $adjustment->date = $data['date'] ?? now();
            $adjustment->account = $data['account'] ?? null;
            $adjustment->reason = $data['reason'] ?? null;
            $adjustment->description = $data['description'] ?? null;
            $adjustment->items = $data['items'] ?? [];
            $adjustment->status = 'completed';
            $adjustment->save();

            // Apply the adjustments
            $this->applyAdjustments($adjustment);

            $this->logAction('inventory_adjustment_created', [
                'adjustment_id' => $adjustment->id,
                'reference_number' => $adjustment->reference_number,
                'mode' => $adjustment->mode_of_adjustment,
            ]);

            return $adjustment;
        });
    }

    /**
     * Apply inventory adjustments.
     *
     * @param InventoryAdjustment $adjustment
     * @return void
     */
    protected function applyAdjustments(InventoryAdjustment $adjustment): void
    {
        foreach ($adjustment->items ?? [] as $itemData) {
            $item = Item::find($itemData['name'] ?? $itemData['item_id'] ?? null);
            if (!$item) {
                continue;
            }

            if ($adjustment->mode_of_adjustment === 'Quantity' || $adjustment->mode_of_adjustment === 0) {
                $newQuantity = floatval($itemData['new_quantity_available'] ?? 0);
                $reason = $adjustment->reason ?? 'Inventory adjustment';
                $this->inventoryService->setStock($item, $newQuantity, $reason);
            }
            // Value adjustments would require additional implementation
        }
    }

    /**
     * Update an inventory adjustment.
     *
     * @param InventoryAdjustment $adjustment
     * @param array $data
     * @return InventoryAdjustment
     * @throws Exception
     */
    public function update(InventoryAdjustment $adjustment, array $data): InventoryAdjustment
    {
        throw new Exception('Inventory adjustments cannot be updated after creation. Create a new adjustment to make changes.');
    }

    /**
     * Delete an inventory adjustment.
     *
     * @param InventoryAdjustment $adjustment
     * @return bool
     * @throws Exception
     */
    public function delete(InventoryAdjustment $adjustment): bool
    {
        // Reverse the adjustments
        $this->reverseAdjustments($adjustment);

        $referenceNumber = $adjustment->reference_number;
        $adjustment->delete();

        $this->logAction('inventory_adjustment_deleted', [
            'reference_number' => $referenceNumber,
        ]);

        return true;
    }

    /**
     * Reverse inventory adjustments.
     *
     * @param InventoryAdjustment $adjustment
     * @return void
     */
    protected function reverseAdjustments(InventoryAdjustment $adjustment): void
    {
        foreach ($adjustment->items ?? [] as $itemData) {
            $item = Item::find($itemData['name'] ?? $itemData['item_id'] ?? null);
            if (!$item) {
                continue;
            }

            if ($adjustment->mode_of_adjustment === 'Quantity' || $adjustment->mode_of_adjustment === 0) {
                // Reverse to original quantity
                $originalQuantity = floatval($itemData['quantity_available'] ?? 0);
                $reason = 'Reversal of adjustment ' . $adjustment->reference_number;
                $this->inventoryService->setStock($item, $originalQuantity, $reason);
            }
        }
    }

    /**
     * Generate a unique reference number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateReferenceNumber(Team $team): string
    {
        $prefix = 'ADJ-';
        $count = InventoryAdjustment::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get adjustments for an item.
     *
     * @param Item $item
     * @return Collection
     */
    public function getItemAdjustments(Item $item): Collection
    {
        return InventoryAdjustment::where('team_id', $item->team_id)
            ->whereJsonContains('items', ['name' => $item->id])
            ->orWhereJsonContains('items', ['item_id' => $item->id])
            ->orderBy('date', 'desc')
            ->get();
    }
}
