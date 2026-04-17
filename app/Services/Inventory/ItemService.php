<?php

namespace App\Services\Inventory;

use App\Models\Item;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Exception;

class ItemService extends BaseService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new item.
     *
     * @param Team $team
     * @param array $data
     * @return Item
     * @throws Exception
     */
    public function create(Team $team, array $data): Item
    {
        return $this->transaction(function () use ($team, $data) {
            $item = new Item();
            $item->team_id = $team->id;
            $item->name = $data['name'];
            $item->item_type = $data['item_type'] ?? 'Goods';
            $item->sku = $data['sku'] ?? $this->generateSku();
            $item->part_number = $data['part_number'] ?? null;
            $item->description = $data['description'] ?? null;
            $item->selling_price = $data['selling_price'] ?? 0;
            $item->cost_price = $data['cost_price'] ?? 0;
            $item->sales_account_id = $data['sales_account_id'] ?? null;
            $item->purchase_account_id = $data['purchase_account_id'] ?? null;
            $item->sales_description = $data['sales_description'] ?? null;
            $item->purchases_description = $data['purchases_description'] ?? null;
            $item->track_inventory_for_this_item = $data['track_inventory_for_this_item'] ?? false;
            $item->opening_stock = $data['stock_on_hand'] ?? $data['opening_stock'] ?? 0;
            $item->reorder_level = $data['reorder_level'] ?? 0;
            $item->warehouse_id = $data['warehouse_id'] ?? null;
            $item->preferred_vendor_id = $data['preferred_vendor_id'] ?? null;
            $item->image = $data['image'] ?? null;
            $item->condition = $data['condition'] ?? null;
            $item->returnable_item = $data['returnable_item'] ?? false;
            $item->dimensions = $data['dimensions'] ?? null;
            $item->weight = $data['weight'] ?? null;
            $item->manufucturer_id = $data['manufucturer_id'] ?? null;
            $item->brand_id = $data['brand_id'] ?? null;
            $item->upc = $data['upc'] ?? null;
            $item->mpn = $data['mpn'] ?? null;
            $item->ean = $data['ean'] ?? null;
            $item->isbn = $data['isbn'] ?? null;
            $item->save();

            $this->logAction('item_created', [
                'item_id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
            ]);

            return $item;
        });
    }

    /**
     * Update an item.
     *
     * @param Item $item
     * @param array $data
     * @return Item
     * @throws Exception
     */
    public function update(Item $item, array $data): Item
    {
        return $this->transaction(function () use ($item, $data) {
            $fillableFields = [
                'name', 'item_type', 'sku', 'part_number', 'description',
                'selling_price', 'cost_price', 'sales_account_id', 'purchase_account_id',
                'sales_description', 'purchases_description', 'track_inventory_for_this_item',
                'opening_stock', 'reorder_level', 'warehouse_id', 'preferred_vendor_id',
                'image', 'condition', 'returnable_item', 'dimensions', 'weight',
                'manufucturer_id', 'brand_id', 'upc', 'mpn', 'ean', 'isbn'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $item->$field = $data[$field];
                }
            }

            // Handle stock_on_hand field mapping
            if (isset($data['stock_on_hand'])) {
                $item->opening_stock = $data['stock_on_hand'];
            }

            $item->save();

            $this->logAction('item_updated', [
                'item_id' => $item->id,
                'name' => $item->name,
            ]);

            return $item;
        });
    }

    /**
     * Delete an item.
     *
     * @param Item $item
     * @return bool
     * @throws Exception
     */
    public function delete(Item $item): bool
    {
        // Check if item is used in any invoices or bills
        $usedInInvoices = \App\Models\ItemsSold::where('item_id', $item->id)->exists();
        $usedInBills = \App\Models\ItemsPurchased::where('item_id', $item->id)->exists();

        if ($usedInInvoices || $usedInBills) {
            throw new Exception('Cannot delete item that has been used in transactions.');
        }

        $item->delete();

        $this->logAction('item_deleted', [
            'item_id' => $item->id,
            'name' => $item->name,
        ]);

        return true;
    }

    /**
     * Generate a unique SKU.
     *
     * @return string
     */
    protected function generateSku(): string
    {
        $length = 12;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sku = '';
        for ($i = 0; $i < $length; $i++) {
            $sku .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $sku;
    }

    /**
     * Get items with low stock.
     *
     * @param Team $team
     * @return Collection
     */
    public function getLowStockItems(Team $team): Collection
    {
        return $this->inventoryService->getLowStockItems($team);
    }

    /**
     * Get items by type.
     *
     * @param Team $team
     * @param string $type
     * @return Collection
     */
    public function getItemsByType(Team $team, string $type): Collection
    {
        return Item::where('team_id', $team->id)
            ->where('item_type', $type)
            ->orderBy('name')
            ->get();
    }
}
