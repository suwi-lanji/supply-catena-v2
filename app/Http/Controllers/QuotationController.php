<?php

namespace App\Http\Controllers;

class QuotationController extends Controller
{
    public function show($tenant_id, $id)
    {
        $tenant = \App\Models\Team::findOrFail($tenant_id);
        $quotation = \App\Models\Quotation::findOrFail($id);
        $customer = \App\Models\Customer::findOrFail($quotation->customer_id);

        return view('pdf-quote', ['record' => $quotation, 'tenant' => $tenant, 'customer' => $customer]);
    }
}
