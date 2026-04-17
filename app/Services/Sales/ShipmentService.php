<?php

namespace App\Services\Sales;

use App\Models\Shipments;
use App\Models\Customer;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Exception;

class ShipmentService extends BaseService
{
    /**
     * Create a new shipment.
     *
     * @param Team $team
     * @param array $data
     * @return Shipments
     * @throws Exception
     */
    public function create(Team $team, array $data): Shipments
    {
        return $this->transaction(function () use ($team, $data) {
            $customer = Customer::findOrFail($data['customer_id']);
            if ($customer->team_id !== $team->id) {
                throw new Exception('Customer does not belong to this team.');
            }

            $shipment = new Shipments();
            $shipment->team_id = $team->id;
            $shipment->customer_id = $data['customer_id'];
            $shipment->shipment_order_number = $data['shipment_order_number'] ?? $this->generateShipmentNumber($team);
            $shipment->shipment_date = $data['shipment_date'] ?? now();
            $shipment->delivery_method_id = $data['delivery_method_id'] ?? null;
            $shipment->tracking_number = $data['tracking_number'] ?? null;
            $shipment->tracking_url = $data['tracking_url'] ?? null;
            $shipment->shipping_charges = $data['shipping_charges'] ?? 0;
            $shipment->packages = $data['packages'] ?? [];
            $shipment->notes = $data['notes'] ?? null;
            $shipment->delivered = $data['delivered'] ?? false;
            $shipment->save();

            $this->logAction('shipment_created', [
                'shipment_id' => $shipment->id,
                'shipment_order_number' => $shipment->shipment_order_number,
                'customer_id' => $customer->id,
            ]);

            return $shipment;
        });
    }

    /**
     * Update a shipment.
     *
     * @param Shipments $shipment
     * @param array $data
     * @return Shipments
     * @throws Exception
     */
    public function update(Shipments $shipment, array $data): Shipments
    {
        return $this->transaction(function () use ($shipment, $data) {
            $fillableFields = [
                'customer_id', 'shipment_order_number', 'shipment_date',
                'delivery_method_id', 'tracking_number', 'tracking_url',
                'shipping_charges', 'packages', 'notes', 'delivered'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $shipment->$field = $data[$field];
                }
            }

            $shipment->save();

            $this->logAction('shipment_updated', [
                'shipment_id' => $shipment->id,
                'shipment_order_number' => $shipment->shipment_order_number,
            ]);

            return $shipment;
        });
    }

    /**
     * Mark a shipment as delivered.
     *
     * @param Shipments $shipment
     * @return Shipments
     * @throws Exception
     */
    public function markAsDelivered(Shipments $shipment): Shipments
    {
        $shipment->delivered = true;
        $shipment->save();

        $this->logAction('shipment_delivered', [
            'shipment_id' => $shipment->id,
            'shipment_order_number' => $shipment->shipment_order_number,
        ]);

        return $shipment;
    }

    /**
     * Delete a shipment.
     *
     * @param Shipments $shipment
     * @return bool
     * @throws Exception
     */
    public function delete(Shipments $shipment): bool
    {
        $shipmentNumber = $shipment->shipment_order_number;
        $shipment->delete();

        $this->logAction('shipment_deleted', [
            'shipment_order_number' => $shipmentNumber,
        ]);

        return true;
    }

    /**
     * Generate a unique shipment number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateShipmentNumber(Team $team): string
    {
        $prefix = 'SHP-';
        $count = Shipments::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get shipments for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getCustomerShipments(Customer $customer): Collection
    {
        return Shipments::where('customer_id', $customer->id)
            ->orderBy('shipment_date', 'desc')
            ->get();
    }
}
