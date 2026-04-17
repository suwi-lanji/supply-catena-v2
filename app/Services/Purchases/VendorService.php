<?php

namespace App\Services\Purchases;

use App\Models\Vendor;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Exception;

class VendorService extends BaseService
{
    /**
     * Create a new vendor.
     *
     * @param Team $team
     * @param array $data
     * @return Vendor
     * @throws Exception
     */
    public function create(Team $team, array $data): Vendor
    {
        return $this->transaction(function () use ($team, $data) {
            $vendor = new Vendor();
            $vendor->team_id = $team->id;
            $vendor->salutation = $data['salutation'] ?? null;
            $vendor->first_name = $data['first_name'] ?? null;
            $vendor->last_name = $data['last_name'] ?? null;
            $vendor->company_name = $data['company_name'] ?? null;
            $vendor->vendor_display_name = $data['vendor_display_name'];
            $vendor->country = $data['country'] ?? null;
            $vendor->city = $data['city'] ?? null;
            $vendor->address = $data['address'] ?? null;
            $vendor->postal_address = $data['postal_address'] ?? null;
            $vendor->email = $data['email'];
            $vendor->phone = $data['phone'] ?? null;
            $vendor->payment_terms = $data['payment_terms'] ?? null;
            $vendor->save();

            $this->logAction('vendor_created', [
                'vendor_id' => $vendor->id,
                'vendor_display_name' => $vendor->vendor_display_name,
                'email' => $vendor->email,
            ]);

            return $vendor;
        });
    }

    /**
     * Update a vendor.
     *
     * @param Vendor $vendor
     * @param array $data
     * @return Vendor
     * @throws Exception
     */
    public function update(Vendor $vendor, array $data): Vendor
    {
        return $this->transaction(function () use ($vendor, $data) {
            $fillableFields = [
                'salutation', 'first_name', 'last_name', 'company_name',
                'vendor_display_name', 'country', 'city', 'address',
                'postal_address', 'email', 'phone', 'payment_terms'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $vendor->$field = $data[$field];
                }
            }

            $vendor->save();

            $this->logAction('vendor_updated', [
                'vendor_id' => $vendor->id,
                'vendor_display_name' => $vendor->vendor_display_name,
            ]);

            return $vendor;
        });
    }

    /**
     * Delete a vendor.
     *
     * @param Vendor $vendor
     * @return bool
     * @throws Exception
     */
    public function delete(Vendor $vendor): bool
    {
        // Check if vendor has bills
        $hasBills = \App\Models\Bill::where('vendor_id', $vendor->id)->exists();

        if ($hasBills) {
            throw new Exception('Cannot delete vendor with existing bills.');
        }

        $vendor->delete();

        $this->logAction('vendor_deleted', [
            'vendor_id' => $vendor->id,
            'vendor_display_name' => $vendor->vendor_display_name,
        ]);

        return true;
    }

    /**
     * Get vendors with outstanding balance.
     *
     * @param Team $team
     * @return Collection
     */
    public function getVendorsWithOutstandingBalance(Team $team): Collection
    {
        return Vendor::where('team_id', $team->id)
            ->whereHas('bills', function ($query) {
                $query->where('balance_due', '>', 0);
            })
            ->with(['bills' => function ($query) {
                $query->where('balance_due', '>', 0);
            }])
            ->get();
    }

    /**
     * Get total payables for a vendor.
     *
     * @param Vendor $vendor
     * @return float
     */
    public function getTotalPayables(Vendor $vendor): float
    {
        return \App\Models\Bill::where('vendor_id', $vendor->id)
            ->where('balance_due', '>', 0)
            ->sum('balance_due');
    }
}
