<?php

namespace App\Services\Sales;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class QuotationService extends BaseService
{
    /**
     * Create a new quotation.
     *
     * @param Team $team
     * @param array $data
     * @return Quotation
     * @throws Exception
     */
    public function create(Team $team, array $data): Quotation
    {
        return $this->transaction(function () use ($team, $data) {
            $customer = Customer::findOrFail($data['customer_id']);
            if ($customer->team_id !== $team->id) {
                throw new Exception('Customer does not belong to this team.');
            }

            $calculations = $this->calculateTotals($data['items'] ?? [], $data['discount'] ?? 0, $data['adjustment'] ?? 0);

            $quotation = new Quotation();
            $quotation->team_id = $team->id;
            $quotation->customer_id = $data['customer_id'];
            $quotation->quotation_number = $data['quotation_number'] ?? $this->generateQuotationNumber($team);
            $quotation->reference_number = $data['reference_number'] ?? null;
            $quotation->report_number = $data['report_number'] ?? null;
            $quotation->stock_in = $data['stock_in'] ?? null;
            $quotation->quotation_date = $data['quotation_date'] ?? now();
            $quotation->expected_shippment_date = $data['expected_shippment_date'] ?? null;
            $quotation->payment_term_id = $data['payment_term_id'] ?? null;
            $quotation->delivery_method_id = $data['delivery_method_id'] ?? null;
            $quotation->sales_person_id = $data['sales_person_id'] ?? null;
            $quotation->inco_term = $data['inco_term'] ?? null;
            $quotation->lead_time = $data['lead_time'] ?? null;
            $quotation->payment_time = $data['payment_time'] ?? null;
            $quotation->items = $data['items'] ?? [];
            $quotation->customer_notes = $data['customer_notes'] ?? null;
            $quotation->terms_and_conditions = $data['terms_and_conditions'] ?? [];
            $quotation->discount = $calculations['discount'];
            $quotation->adjustment = $calculations['adjustment'];
            $quotation->sub_total = $calculations['sub_total'];
            $quotation->shipment_charges = $data['shipment_charges'] ?? 0;
            $quotation->total = $calculations['total'];
            $quotation->status = $data['status'] ?? 'draft';
            $quotation->save();

            $this->logAction('quotation_created', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'customer_id' => $customer->id,
                'total' => $quotation->total,
            ]);

            return $quotation;
        });
    }

    /**
     * Update a quotation.
     *
     * @param Quotation $quotation
     * @param array $data
     * @return Quotation
     * @throws Exception
     */
    public function update(Quotation $quotation, array $data): Quotation
    {
        return $this->transaction(function () use ($quotation, $data) {
            $calculations = $this->calculateTotals($data['items'] ?? $quotation->items, $data['discount'] ?? $quotation->discount, $data['adjustment'] ?? $quotation->adjustment);

            $fillableFields = [
                'customer_id', 'quotation_number', 'reference_number', 'report_number',
                'stock_in', 'quotation_date', 'expected_shippment_date', 'payment_term_id',
                'delivery_method_id', 'sales_person_id', 'inco_term', 'lead_time',
                'payment_time', 'items', 'customer_notes', 'terms_and_conditions',
                'discount', 'adjustment', 'sub_total', 'shipment_charges', 'total', 'status'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $quotation->$field = $data[$field];
                }
            }

            $quotation->sub_total = $calculations['sub_total'];
            $quotation->discount = $calculations['discount'];
            $quotation->adjustment = $calculations['adjustment'];
            $quotation->total = $calculations['total'];

            $quotation->save();

            $this->logAction('quotation_updated', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
            ]);

            return $quotation;
        });
    }

    /**
     * Delete a quotation.
     *
     * @param Quotation $quotation
     * @return bool
     * @throws Exception
     */
    public function delete(Quotation $quotation): bool
    {
        $quotationNumber = $quotation->quotation_number;
        $quotation->delete();

        $this->logAction('quotation_deleted', [
            'quotation_number' => $quotationNumber,
        ]);

        return true;
    }

    /**
     * Convert quotation to sales order.
     *
     * @param Quotation $quotation
     * @return \App\Models\SalesOrder
     * @throws Exception
     */
    public function convertToSalesOrder(Quotation $quotation): \App\Models\SalesOrder
    {
        return $this->transaction(function () use ($quotation) {
            $salesOrder = new \App\Models\SalesOrder();
            $salesOrder->team_id = $quotation->team_id;
            $salesOrder->customer_id = $quotation->customer_id;
            $salesOrder->sales_order_number = 'SO-' . str_pad(\App\Models\SalesOrder::where('team_id', $quotation->team_id)->count() + 1, 5, '0', STR_PAD_LEFT);
            $salesOrder->reference_number = $quotation->reference_number;
            $salesOrder->sales_order_date = now();
            $salesOrder->expected_shippment_date = $quotation->expected_shippment_date;
            $salesOrder->payment_term_id = $quotation->payment_term_id;
            $salesOrder->delivery_method_id = $quotation->delivery_method_id;
            $salesOrder->sales_person_id = $quotation->sales_person_id;
            $salesOrder->items = $quotation->items;
            $salesOrder->customer_notes = $quotation->customer_notes;
            $salesOrder->terms_and_conditions = $quotation->terms_and_conditions;
            $salesOrder->discount = $quotation->discount;
            $salesOrder->adjustment = $quotation->adjustment;
            $salesOrder->sub_total = $quotation->sub_total;
            $salesOrder->shipment_charges = $quotation->shipment_charges;
            $salesOrder->total = $quotation->total;
            $salesOrder->status = 'draft';
            $salesOrder->quotation_id = $quotation->id;
            $salesOrder->save();

            $quotation->status = 'converted';
            $quotation->save();

            $this->logAction('quotation_converted_to_sales_order', [
                'quotation_id' => $quotation->id,
                'sales_order_id' => $salesOrder->id,
            ]);

            return $salesOrder;
        });
    }

    /**
     * Calculate totals for a quotation.
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
     * Generate a unique quotation number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateQuotationNumber(Team $team): string
    {
        $prefix = 'QO-' . now()->format('my');
        $count = Quotation::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get quotations for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getCustomerQuotations(Customer $customer): Collection
    {
        return Quotation::where('customer_id', $customer->id)
            ->orderBy('quotation_date', 'desc')
            ->get();
    }
}
