<?php

namespace App\Observers;

use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // Prepare the request data
        $requestData = [
            'tpin' => env('ZRA_TPIN'),
            'bhfId' => $customer->branch_id,
            'custNo' => $customer->phone,
            'custNm' => $customer->first_name.' '.$customer->lastName,
            'adrs' => $customer->billing_street_1.' '.$customer->billing_city.' '.$customer->billing_province.' '.$customer->billing_country,
            'email' => $customer->email,
            'useYn' => $customer->useYn,
            'remark' => $customer->remark ?? 'N/A',
            'regrNm' => $customer->regrNm,
            'regrId' => $customer->regr_id,
            'modrNm' => $customer->modrNm,
            'modrId' => $customer->modr_id,
        ];

        // Send request to the API
        try {
            $response = Http::post(env('ZRA_API_URL').'branches/saveBrancheCustomers', $requestData);

            // Check if the API request was successful
            if ($response->successful()) {
                // Handle success (optional)
            } else {
                throw new HttpException($response->status(), 'Failed to create customer on API');
            }
        } catch (\Exception $e) {
            // Log the error (optional)
            \Log::error('Failed to save branch customer: '.$e->getMessage());

            // Delete the customer from the database
            $customer->delete();
        }
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
