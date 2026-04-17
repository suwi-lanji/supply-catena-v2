<?php

namespace App\Services\Inventory;

use App\Models\TransferOrder;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Exception;

class TransferOrderService extends BaseService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new transfer order.
     *
     * @param Team $team
     * @param array $data
     * @return TransferOrder
     * @throws Exception
     */
    public function create(Team $team, array $data): TransferOrder
    {
        return $this->transaction(function () use ($team, $data) {
            $sourceWarehouse = Warehouse::findOrFail($data['source_warehouse_id']);
            $destinationWarehouse = Warehouse::findOrFail($data['destination_warehouse_id']);

            if ($sourceWarehouse->team_id !== $team->id || $destinationWarehouse->team_id !== $team->id) {
                throw new Exception('Warehouses do not belong to this team.');
            }

            $transferOrder = new TransferOrder();
            $transferOrder->team_id = $team->id;
            $transferOrder->transfer_order_number = $data['transfer_order_number'] ?? $this->generateTransferOrderNumber($team);
            $transferOrder->date = $data['date'] ?? now();
            $transferOrder->source_warehouse_id = $data['source_warehouse_id'];
            $transferOrder->destination_warehouse_id = $data['destination_warehouse_id'];
            $transferOrder->reason = $data['reason'] ?? null;
            $transferOrder->costs = $data['costs'] ?? [];
            $transferOrder->items = $data['items'] ?? [];
            $transferOrder->delivered = $data['delivered'] ?? false;
            $transferOrder->save();

            // If delivered immediately, process the transfer
            if ($transferOrder->delivered) {
                $this->processTransfer($transferOrder);
            }

            $this->logAction('transfer_order_created', [
                'transfer_order_id' => $transferOrder->id,
                'transfer_order_number' => $transferOrder->transfer_order_number,
            ]);

            return $transferOrder;
        });
    }

    /**
     * Update a transfer order.
     *
     * @param TransferOrder $transferOrder
     * @param array $data
     * @return TransferOrder
     * @throws Exception
     */
    public function update(TransferOrder $transferOrder, array $data): TransferOrder
    {
        if ($transferOrder->delivered) {
            throw new Exception('Cannot update a delivered transfer order.');
        }

        return $this->transaction(function () use ($transferOrder, $data) {
            $fillableFields = [
                'transfer_order_number', 'date', 'source_warehouse_id',
                'destination_warehouse_id', 'reason', 'costs', 'items', 'delivered'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $transferOrder->$field = $data[$field];
                }
            }

            $wasDelivered = $transferOrder->delivered;
            $transferOrder->save();

            // If newly delivered, process the transfer
            if (!$wasDelivered && $transferOrder->delivered) {
                $this->processTransfer($transferOrder);
            }

            $this->logAction('transfer_order_updated', [
                'transfer_order_id' => $transferOrder->id,
                'transfer_order_number' => $transferOrder->transfer_order_number,
            ]);

            return $transferOrder;
        });
    }

    /**
     * Mark a transfer order as delivered.
     *
     * @param TransferOrder $transferOrder
     * @return TransferOrder
     * @throws Exception
     */
    public function markAsDelivered(TransferOrder $transferOrder): TransferOrder
    {
        if ($transferOrder->delivered) {
            throw new Exception('Transfer order is already delivered.');
        }

        return $this->transaction(function () use ($transferOrder) {
            $this->processTransfer($transferOrder);

            $transferOrder->delivered = true;
            $transferOrder->save();

            $this->logAction('transfer_order_delivered', [
                'transfer_order_id' => $transferOrder->id,
                'transfer_order_number' => $transferOrder->transfer_order_number,
            ]);

            return $transferOrder;
        });
    }

    /**
     * Process the transfer by moving inventory.
     *
     * @param TransferOrder $transferOrder
     * @return void
     */
    protected function processTransfer(TransferOrder $transferOrder): void
    {
        foreach ($transferOrder->items ?? [] as $itemData) {
            $itemId = $itemData['item_name'] ?? $itemData['item_id'] ?? null;
            $quantity = floatval($itemData['transfer_quantity'] ?? 0);

            if (!$itemId || $quantity <= 0) {
                continue;
            }

            $item = Item::find($itemId);
            if (!$item) {
                continue;
            }

            $this->inventoryService->transferStock(
                $item,
                Warehouse::find($transferOrder->source_warehouse_id),
                Warehouse::find($transferOrder->destination_warehouse_id),
                $quantity,
                $transferOrder->transfer_order_number
            );
        }
    }

    /**
     * Delete a transfer order.
     *
     * @param TransferOrder $transferOrder
     * @return bool
     * @throws Exception
     */
    public function delete(TransferOrder $transferOrder): bool
    {
        if ($transferOrder->delivered) {
            throw new Exception('Cannot delete a delivered transfer order.');
        }

        $transferOrderNumber = $transferOrder->transfer_order_number;
        $transferOrder->delete();

        $this->logAction('transfer_order_deleted', [
            'transfer_order_number' => $transferOrderNumber,
        ]);

        return true;
    }

    /**
     * Generate a unique transfer order number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateTransferOrderNumber(Team $team): string
    {
        $prefix = 'TO-';
        $count = TransferOrder::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
