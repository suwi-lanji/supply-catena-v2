<?php

namespace App\Services\Purchases;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Team;
use App\Services\BaseService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Collection;
use Exception;

class PurchaseOrderService extends BaseService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new purchase order.
     *
     * @param Team $team
     * @param array $data
     * @return PurchaseOrder
     * @throws Exception
     */
    public function create(Team $team, array $data): PurchaseOrder
    {
        return $this->transaction(function () use ($team, $data) {
            $vendor = Vendor::findOrFail($data['vendor_id']);
            if ($vendor->team_id !== $team->id) {
                throw new Exception('Vendor does not belong to this team.');
            }

            $calculations = $this->calculateTotals($data['items'] ?? [], $data['discount'] ?? 0, $data['adjustment'] ?? 0);

            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->team_id = $team->id;
            $purchaseOrder->vendor_id = $data['vendor_id'];
            $purchaseOrder->purchase_order_number = $data['purchase_order_number'] ?? $this->generatePurchaseOrderNumber($team);
            $purchaseOrder->reference_number = $data['reference_number'] ?? null;
            $purchaseOrder->purchase_order_date = $data['purchase_order_date'] ?? now();
            $purchaseOrder->expected_delivery_date = $data['expected_delivery_date'] ?? null;
            $purchaseOrder->payment_terms = $data['payment_terms'] ?? null;
            $purchaseOrder->shipment_preference = $data['shipment_preference'] ?? null;
            $purchaseOrder->delivery_street = $data['delivery_street'] ?? null;
            $purchaseOrder->delivery_city = $data['delivery_city'] ?? null;
            $purchaseOrder->delivery_province = $data['delivery_province'] ?? null;
            $purchaseOrder->delivery_country = $data['delivery_country'] ?? null;
            $purchaseOrder->delivery_phone = $data['delivery_phone'] ?? null;
            $purchaseOrder->items = $data['items'] ?? [];
            $purchaseOrder->customer_notes = $data['customer_notes'] ?? null;
            $purchaseOrder->terms_and_conditions = $data['terms_and_conditions'] ?? [];
            $purchaseOrder->discount = $calculations['discount'];
            $purchaseOrder->adjustment = $calculations['adjustment'];
            $purchaseOrder->sub_total = $calculations['sub_total'];
            $purchaseOrder->shipment_charges = $data['shipment_charges'] ?? 0;
            $purchaseOrder->total = $calculations['total'];
            $purchaseOrder->status = $data['status'] ?? 'draft';
            $purchaseOrder->received = false;
            $purchaseOrder->billed = false;
            $purchaseOrder->save();

            $this->logAction('purchase_order_created', [
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_number' => $purchaseOrder->purchase_order_number,
                'vendor_id' => $vendor->id,
                'total' => $purchaseOrder->total,
            ]);

            return $purchaseOrder;
        });
    }

    /**
     * Update a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param array $data
     * @return PurchaseOrder
     * @throws Exception
     */
    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        if (!in_array($purchaseOrder->status, ['draft', 'open'])) {
            throw new Exception('Only draft purchase orders can be updated.');
        }

        return $this->transaction(function () use ($purchaseOrder, $data) {
            $calculations = $this->calculateTotals($data['items'] ?? $purchaseOrder->items, $data['discount'] ?? $purchaseOrder->discount, $data['adjustment'] ?? $purchaseOrder->adjustment);

            $fillableFields = [
                'vendor_id', 'purchase_order_number', 'reference_number', 'purchase_order_date',
                'expected_delivery_date', 'payment_terms', 'shipment_preference',
                'delivery_street', 'delivery_city', 'delivery_province', 'delivery_country',
                'delivery_phone', 'items', 'customer_notes', 'terms_and_conditions',
                'discount', 'adjustment', 'sub_total', 'shipment_charges', 'total', 'status'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $purchaseOrder->$field = $data[$field];
                }
            }

            $purchaseOrder->sub_total = $calculations['sub_total'];
            $purchaseOrder->discount = $calculations['discount'];
            $purchaseOrder->adjustment = $calculations['adjustment'];
            $purchaseOrder->total = $calculations['total'];

            $purchaseOrder->save();

            $this->logAction('purchase_order_updated', [
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_number' => $purchaseOrder->purchase_order_number,
            ]);

            return $purchaseOrder;
        });
    }

    /**
     * Mark a purchase order as received.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrder
     * @throws Exception
     */
    public function markAsReceived(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder->received = true;
        $purchaseOrder->status = 'received';
        $purchaseOrder->save();

        $this->logAction('purchase_order_received', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_order_number' => $purchaseOrder->purchase_order_number,
        ]);

        return $purchaseOrder;
    }

    /**
     * Mark a purchase order as billed.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrder
     * @throws Exception
     */
    public function markAsBilled(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder->billed = true;
        $purchaseOrder->status = 'billed';
        $purchaseOrder->save();

        $this->logAction('purchase_order_billed', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_order_number' => $purchaseOrder->purchase_order_number,
        ]);

        return $purchaseOrder;
    }

    /**
     * Cancel a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrder
     * @throws Exception
     */
    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->received || $purchaseOrder->billed) {
            throw new Exception('Cannot cancel received or billed purchase orders.');
        }

        $purchaseOrder->status = 'cancelled';
        $purchaseOrder->save();

        $this->logAction('purchase_order_cancelled', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_order_number' => $purchaseOrder->purchase_order_number,
        ]);

        return $purchaseOrder;
    }

    /**
     * Delete a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return bool
     * @throws Exception
     */
    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        if (!in_array($purchaseOrder->status, ['draft', 'cancelled'])) {
            throw new Exception('Only draft or cancelled purchase orders can be deleted.');
        }

        $purchaseOrderNumber = $purchaseOrder->purchase_order_number;
        $purchaseOrder->delete();

        $this->logAction('purchase_order_deleted', [
            'purchase_order_number' => $purchaseOrderNumber,
        ]);

        return true;
    }

    /**
     * Calculate totals for a purchase order.
     *
     * @param array $items
     * @param float $discountPercent
     * @param float $adjustment
     * @return array
     */
    protected function calculateTotals(array $items, float $discountPercent = 0, float $adjustment = 0): array
    {
        $subTotal = 0;

        foreach ($items as $item) {
            $lineTotal = floatval($item['quantity'] ?? 0) * floatval($item['rate'] ?? 0);
            $subTotal += floatval($item['amount'] ?? $lineTotal);
        }

        $total = $subTotal;

        if ($discountPercent > 0) {
            $total = $total - ($discountPercent / 100 * $total);
        }

        $total += $adjustment;

        return [
            'sub_total' => round($subTotal, 2),
            'discount' => round($discountPercent, 2),
            'adjustment' => round($adjustment, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Generate a unique purchase order number.
     *
     * @param Team $team
     * @return string
     */
    protected function generatePurchaseOrderNumber(Team $team): string
    {
        $prefix = 'PO-';
        $count = PurchaseOrder::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get purchase orders for a vendor.
     *
     * @param Vendor $vendor
     * @return Collection
     */
    public function getVendorPurchaseOrders(Vendor $vendor): Collection
    {
        return PurchaseOrder::where('vendor_id', $vendor->id)
            ->orderBy('purchase_order_date', 'desc')
            ->get();
    }

    /**
     * Get unbilled purchase orders for a vendor.
     *
     * @param Vendor $vendor
     * @return Collection
     */
    public function getUnbilledPurchaseOrders(Vendor $vendor): Collection
    {
        return PurchaseOrder::where('vendor_id', $vendor->id)
            ->where('billed', false)
            ->orderBy('purchase_order_date', 'desc')
            ->get();
    }
}
