<?php

namespace App\Services\Sales;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Team;
use App\Services\BaseService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Collection;
use Exception;

class SalesOrderService extends BaseService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new sales order.
     *
     * @param Team $team
     * @param array $data
     * @return SalesOrder
     * @throws Exception
     */
    public function create(Team $team, array $data): SalesOrder
    {
        return $this->transaction(function () use ($team, $data) {
            $customer = Customer::findOrFail($data['customer_id']);
            if ($customer->team_id !== $team->id) {
                throw new Exception('Customer does not belong to this team.');
            }

            $calculations = $this->calculateTotals($data['items'] ?? [], $data['discount'] ?? 0, $data['adjustment'] ?? 0);

            $salesOrder = new SalesOrder();
            $salesOrder->team_id = $team->id;
            $salesOrder->customer_id = $data['customer_id'];
            $salesOrder->sales_order_number = $data['sales_order_number'] ?? $this->generateSalesOrderNumber($team);
            $salesOrder->reference_number = $data['reference_number'] ?? null;
            $salesOrder->purchase_order_number = $data['purchase_order_number'] ?? null;
            $salesOrder->sales_order_date = $data['sales_order_date'] ?? now();
            $salesOrder->expected_shippment_date = $data['expected_shippment_date'] ?? null;
            $salesOrder->payment_term_id = $data['payment_term_id'] ?? null;
            $salesOrder->delivery_method_id = $data['delivery_method_id'] ?? null;
            $salesOrder->sales_person_id = $data['sales_person_id'] ?? null;
            $salesOrder->items = $data['items'] ?? [];
            $salesOrder->customer_notes = $data['customer_notes'] ?? null;
            $salesOrder->terms_and_conditions = $data['terms_and_conditions'] ?? [];
            $salesOrder->discount = $calculations['discount'];
            $salesOrder->adjustment = $calculations['adjustment'];
            $salesOrder->sub_total = $calculations['sub_total'];
            $salesOrder->shipment_charges = $data['shipment_charges'] ?? 0;
            $salesOrder->total = $calculations['total'];
            $salesOrder->status = $data['status'] ?? 'draft';
            $salesOrder->quotation_id = $data['quotation_id'] ?? null;
            $salesOrder->save();

            $this->logAction('sales_order_created', [
                'sales_order_id' => $salesOrder->id,
                'sales_order_number' => $salesOrder->sales_order_number,
                'customer_id' => $customer->id,
                'total' => $salesOrder->total,
            ]);

            return $salesOrder;
        });
    }

    /**
     * Update a sales order.
     *
     * @param SalesOrder $salesOrder
     * @param array $data
     * @return SalesOrder
     * @throws Exception
     */
    public function update(SalesOrder $salesOrder, array $data): SalesOrder
    {
        if (!in_array($salesOrder->status, ['draft', 'open'])) {
            throw new Exception('Only draft sales orders can be updated.');
        }

        return $this->transaction(function () use ($salesOrder, $data) {
            $calculations = $this->calculateTotals($data['items'] ?? $salesOrder->items, $data['discount'] ?? $salesOrder->discount, $data['adjustment'] ?? $salesOrder->adjustment);

            $fillableFields = [
                'customer_id', 'sales_order_number', 'reference_number', 'purchase_order_number',
                'sales_order_date', 'expected_shippment_date', 'payment_term_id',
                'delivery_method_id', 'sales_person_id', 'items', 'customer_notes',
                'terms_and_conditions', 'discount', 'adjustment', 'sub_total',
                'shipment_charges', 'total', 'status'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $salesOrder->$field = $data[$field];
                }
            }

            $salesOrder->sub_total = $calculations['sub_total'];
            $salesOrder->discount = $calculations['discount'];
            $salesOrder->adjustment = $calculations['adjustment'];
            $salesOrder->total = $calculations['total'];

            $salesOrder->save();

            $this->logAction('sales_order_updated', [
                'sales_order_id' => $salesOrder->id,
                'sales_order_number' => $salesOrder->sales_order_number,
            ]);

            return $salesOrder;
        });
    }

    /**
     * Confirm a sales order.
     *
     * @param SalesOrder $salesOrder
     * @return SalesOrder
     * @throws Exception
     */
    public function confirm(SalesOrder $salesOrder): SalesOrder
    {
        if (!in_array($salesOrder->status, ['draft', 'open'])) {
            throw new Exception('Only draft sales orders can be confirmed.');
        }

        $salesOrder->status = 'confirmed';
        $salesOrder->save();

        $this->logAction('sales_order_confirmed', [
            'sales_order_id' => $salesOrder->id,
            'sales_order_number' => $salesOrder->sales_order_number,
        ]);

        return $salesOrder;
    }

    /**
     * Cancel a sales order.
     *
     * @param SalesOrder $salesOrder
     * @return SalesOrder
     * @throws Exception
     */
    public function cancel(SalesOrder $salesOrder): SalesOrder
    {
        if (in_array($salesOrder->status, ['shipped', 'delivered'])) {
            throw new Exception('Cannot cancel shipped or delivered sales orders.');
        }

        $salesOrder->status = 'cancelled';
        $salesOrder->save();

        $this->logAction('sales_order_cancelled', [
            'sales_order_id' => $salesOrder->id,
            'sales_order_number' => $salesOrder->sales_order_number,
        ]);

        return $salesOrder;
    }

    /**
     * Delete a sales order.
     *
     * @param SalesOrder $salesOrder
     * @return bool
     * @throws Exception
     */
    public function delete(SalesOrder $salesOrder): bool
    {
        if (!in_array($salesOrder->status, ['draft', 'cancelled'])) {
            throw new Exception('Only draft or cancelled sales orders can be deleted.');
        }

        $salesOrderNumber = $salesOrder->sales_order_number;
        $salesOrder->delete();

        $this->logAction('sales_order_deleted', [
            'sales_order_number' => $salesOrderNumber,
        ]);

        return true;
    }

    /**
     * Calculate totals for a sales order.
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
     * Generate a unique sales order number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateSalesOrderNumber(Team $team): string
    {
        $prefix = 'SO-' . now()->format('my');
        $count = SalesOrder::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get sales orders for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getCustomerSalesOrders(Customer $customer): Collection
    {
        return SalesOrder::where('customer_id', $customer->id)
            ->orderBy('sales_order_date', 'desc')
            ->get();
    }
}
