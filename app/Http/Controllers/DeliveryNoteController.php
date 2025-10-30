<?php

namespace App\Http\Controllers;

class DeliveryNoteController extends Controller
{
    public function show($tenant_id, $id)
    {
        $tenant = \App\Models\Team::findOrFail($tenant_id);
        $deliveryNote = \App\Models\DeliveryNote::findOrFail($id);
        $customer = \App\Models\Customer::findOrFail($deliveryNote->customer_id);

        return view('pdf-delivery-note', ['record' => $deliveryNote, 'tenant' => $tenant, 'customer' => $customer]);
    }
}
