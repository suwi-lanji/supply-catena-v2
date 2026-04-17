<?php

namespace App\Services\Sales;

use App\Models\Customer;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Exception;

class CustomerService extends BaseService
{
    /**
     * Create a new customer.
     *
     * @param Team $team
     * @param array $data
     * @return Customer
     * @throws Exception
     */
    public function create(Team $team, array $data): Customer
    {
        return $this->transaction(function () use ($team, $data) {
            $customer = new Customer();
            $customer->team_id = $team->id;
            $customer->customer_type = $data['customer_type'] ?? 'Business';
            $customer->salutation = $data['salutation'] ?? null;
            $customer->first_name = $data['first_name'];
            $customer->last_name = $data['last_name'];
            $customer->company_name = $data['company_name'] ?? null;
            $customer->company_display_name = $data['company_display_name'] ?? $data['company_name'] ?? null;
            $customer->vendor_number = $data['vendor_number'] ?? null;
            $customer->email = $data['email'];
            $customer->phone = $data['phone'];
            $customer->tpin = $data['tpin'] ?? null;
            $customer->branch_id = $data['branch_id'] ?? null;
            $customer->useYn = $data['useYn'] ?? true;
            $customer->regrNm = $data['regrNm'] ?? auth()->user()->name ?? null;
            $customer->regr_id = $data['regr_id'] ?? auth()->id();
            $customer->modrNm = $data['modrNm'] ?? auth()->user()->name ?? null;
            $customer->modr_id = $data['modr_id'] ?? auth()->id();
            $customer->payment_terms = $data['payment_terms'] ?? null;

            // Billing address
            $customer->billing_street_1 = $data['billing_street_1'] ?? null;
            $customer->billing_street_2 = $data['billing_street_2'] ?? null;
            $customer->billing_city = $data['billing_city'] ?? null;
            $customer->billing_province = $data['billing_province'] ?? null;
            $customer->billing_country = $data['billing_country'] ?? null;
            $customer->billing_phone = $data['billing_phone'] ?? null;

            // Shipping address
            $customer->shipping_street_1 = $data['shipping_street_1'] ?? null;
            $customer->shipping_street_2 = $data['shipping_street_2'] ?? null;
            $customer->shipping_city = $data['shipping_city'] ?? null;
            $customer->shipping_province = $data['shipping_province'] ?? null;
            $customer->shipping_country = $data['shipping_country'] ?? null;
            $customer->shipping_phone = $data['shipping_phone'] ?? null;

            $customer->remarks = $data['remarks'] ?? null;
            $customer->save();

            $this->logAction('customer_created', [
                'customer_id' => $customer->id,
                'company_name' => $customer->company_name,
                'email' => $customer->email,
            ]);

            return $customer;
        });
    }

    /**
     * Update a customer.
     *
     * @param Customer $customer
     * @param array $data
     * @return Customer
     * @throws Exception
     */
    public function update(Customer $customer, array $data): Customer
    {
        return $this->transaction(function () use ($customer, $data) {
            $fillableFields = [
                'customer_type', 'salutation', 'first_name', 'last_name',
                'company_name', 'company_display_name', 'vendor_number',
                'email', 'phone', 'tpin', 'branch_id', 'useYn',
                'regrNm', 'regr_id', 'modrNm', 'modr_id', 'payment_terms',
                'billing_street_1', 'billing_street_2', 'billing_city',
                'billing_province', 'billing_country', 'billing_phone',
                'shipping_street_1', 'shipping_street_2', 'shipping_city',
                'shipping_province', 'shipping_country', 'shipping_phone',
                'remarks'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $customer->$field = $data[$field];
                }
            }

            $customer->save();

            $this->logAction('customer_updated', [
                'customer_id' => $customer->id,
                'company_name' => $customer->company_name,
            ]);

            return $customer;
        });
    }

    /**
     * Delete a customer.
     *
     * @param Customer $customer
     * @return bool
     * @throws Exception
     */
    public function delete(Customer $customer): bool
    {
        // Check if customer has invoices
        $hasInvoices = \App\Models\Invoices::where('customer_id', $customer->id)->exists();

        if ($hasInvoices) {
            throw new Exception('Cannot delete customer with existing invoices.');
        }

        $customer->delete();

        $this->logAction('customer_deleted', [
            'customer_id' => $customer->id,
            'company_name' => $customer->company_name,
        ]);

        return true;
    }

    /**
     * Get customers with outstanding balance.
     *
     * @param Team $team
     * @return Collection
     */
    public function getCustomersWithOutstandingBalance(Team $team): Collection
    {
        return Customer::where('team_id', $team->id)
            ->whereHas('invoices', function ($query) {
                $query->where('balance_due', '>', 0);
            })
            ->with(['invoices' => function ($query) {
                $query->where('balance_due', '>', 0);
            }])
            ->get();
    }

    /**
     * Get total receivables for a customer.
     *
     * @param Customer $customer
     * @return float
     */
    public function getTotalReceivables(Customer $customer): float
    {
        return \App\Models\Invoices::where('customer_id', $customer->id)
            ->where('balance_due', '>', 0)
            ->sum('balance_due');
    }
}
