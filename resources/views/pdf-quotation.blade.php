@php
$customer = \App\Models\Customer::where('id', $record->customer_id)->first();
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = storage_path("app/public/" . str_replace("/content/", "/", $tenant->logo));
$terms = \App\Models\PaymentTerm::find($record->payment_term_id);
$vat = 0;
$discount = 0;
@endphp

<style>
    body {
        font-family: "Figtree", sans-serif;
        margin: 0;
        padding: 0;
        font-size: 0.7em;
        color: #000;
        background-color: #fff;
    }
    
    .invoice {
        padding: 2rem;
        color: #000;
        background-color: #fff;
    }
    
    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }
    
    .flex-row {
        width: 100%;
    }
    
    .justify-between {
        width: 100%;
    }
    
    .left-section {
        float: left;
        width: 60%;
    }
    
    .right-section {
        float: right;
        width: 35%;
        text-align: left;
    }
    
    .mb-12 { margin-bottom: 3rem; }
    .mb-8 { margin-bottom: 2rem; }
    .mb-5 { margin-bottom: 1.25rem; }
    .mr-3 { margin-right: 0.75rem; }
    .mt-2 { margin-top: 0.5rem; }
    .p-8 { padding: 2rem; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .text-xl { font-size: 1.25rem; }
    .text-sm { font-size: 0.875rem; }
    .font-semibold { font-weight: 600; }
    .w-full { width: 100%; }
    .w-half { width: 48%; }
    
    table {
        border-collapse: collapse;
        width: 100%;
    }
    
    .table-auto { table-layout: auto; }
    
    .border { border-width: 1px; }
    .border-gray-300 { border-color: #d1d5db; }
    
    th, td {
        padding: 0.25rem 0.5rem;
        border: 1px solid #d1d5db;
    }
    
    .bg-gray-100 { background-color: #f3f4f6; }
    .bg-gray-800 { background-color: #1f2937; color: #fff; }
    
    address { font-style: normal; }
    
    .delivery-address {
        width: 60%;
        float: left;
    }
    
    .contact-info {
        width: 35%;
        float: right;
        text-align: left;
    }
    
    .quotation-number {
        width: 30%;
        float: left;
    }
    
    .totals-table {
        width: 65%;
        float: right;
    }
</style>

<div class="invoice">
    <!-- Header Section -->
    <div class="clearfix mb-12">
        <div class="left-section">
            <div style="float: left; margin-right: 0.75rem;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="Logo" style="width:150px;height:150px"/>
            </div>
            <div style="float: left;">
                <h5 class="font-semibold text-xl">{{ $tenant->portal_name }}</h5>
                <address class="mt-2 text-sm">
                    <span>{{ $tenant->street_1 }}</span><br/>
                    <span>{{ $tenant->city }}, {{ $tenant->province }}, {{ $tenant->business_location }}</span><br/>
                </address>
            </div>
        </div>
        <div class="right-section">
            <h5 class="font-semibold text-xl text-center">QUOTATION</h5>
            <p><strong>Bank:</strong> {{ $terms->bank }}</p>
            <p><strong>A/C Name:</strong> {{ $terms->account_name }}</p>
            <p><strong>Account No:</strong> {{ $terms->account_number }}</p>
            <p><strong>Branch:</strong> {{ $terms->branch }}</p>
            <p><strong>Swift Code:</strong> {{ $terms->swift_code }}</p>
            <p><strong>Branch No:</strong> {{ $terms->branch_number }}</p>
        </div>
    </div>

    <!-- Delivery Address Section -->
    <div class="clearfix mb-5">
        <div class="delivery-address">
            <table class="table-auto border border-gray-300">
                <thead>
                    <tr>
                        <th colspan="2" class="bg-gray-100 text-center">DELIVERY ADDRESS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <address>
                                <span>{{ $customer->company_display_name }}</span><br/>
                                <span>{{ $customer->billing_street_1 }}, {{ $customer->billing_city }}, {{ $customer->billing_country }}</span><br/>
                                <span>{{ $customer->billing_city }}</span><br/>
                                <span>Phone: {{ $customer->phone }}</span>
                            </address>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="contact-info">
            <p>OFFICE CELL: {{ $tenant->phone }}</p>
            <p>EMAIL: {{ $tenant->email }}</p>
        </div>
    </div>

    <!-- Quotation Details Table -->
    <div class="mb-8">
        <table class="table-auto w-full border border-gray-300">
            <thead>
                <tr>
                    <th class="bg-gray-100">VALID DATE</th>
                    <th class="bg-gray-100">CLIENT</th>
                    <th class="bg-gray-100">REPORT NO.</th>
                    <th class="bg-gray-100">INCO TERM</th>
                    <th class="bg-gray-100">STOCK IN</th>
                    <th class="bg-gray-100">LEAD TIME</th>
                    <th class="bg-gray-100">PAYMENT TERM</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">
                        {{ date("Y/m/d", strtotime($record->quotation_date)) }} -
                        {{ $record->expected_shippment_date != null ? date("Y/m/d", strtotime($record->expected_shippment_date)) : "" }}
                    </td>
                    <td class="text-center">{{ $customer->company_display_name }}</td>
                    <td class="text-center">{{ $record->report_number }}</td>
                    <td class="text-center">{{ $record->inco_term}}</td>
                    <td class="text-center">{{ $record->stock_in }}</td>
                    <td class="text-center">{{ $record->lead_time }}</td>
                    <td class="text-center">{{ $record->payment_time }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Invoice Items Table -->
    <div class="mb-12">
        <table class="table-auto w-full text-sm border border-gray-300">
            <thead class="bg-gray-800">
                <tr>
                    <th class="bg-gray-100">ITEM</th>
                    <th class="bg-gray-100">MATERIAL NO.</th>
                    <th class="bg-gray-100">PART NO.</th>
                    <th class="bg-gray-100">DESCRIPTION</th>
                    <th class="bg-gray-100">LEAD TIME</th>
                    <th class="bg-gray-100">QTY</th>
                    <th class="bg-gray-100">UNIT PRICE (EXCL)</th>
                    <th class="bg-gray-100">UNIT PRICE (INCL)</th>
                    <th class="bg-gray-100">DISC %</th>
                    <th class="bg-gray-100">TOTAL PRICE (EXCL)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $index => $item)
                @php
                    $itemModel = \App\Models\Item::find($item['item']);
                    $vat += $item["tax"] ?? 0;
                    $discount += $item["discount"] ?? 0;
                @endphp
                @if ($itemModel)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item["sku"] ?? "N/A" }}</td>
                    <td class="text-center">{{ $itemModel->part_number ?? "N/A" }}</td>
                    <td class="text-center">{{ $itemModel->description ?? "N/A" }}</td>
                    <td class="text-center">{{ $item["lead_time"] ?? "N/A" }}</td>
                    <td class="text-center">{{ $item["quantity"] ?? "0" }}</td>
                    <td class="text-center">{{ number_format($item["rate"], 2) }}</td>
                    <td class="text-center">{{ number_format($item["rate"], 2) }}</td>
                    <td class="text-center">{{ $item["discount"] ?? "0" }}%</td>
                    <td class="text-center">{{ number_format($item["amount"], 2) }}</td>
                </tr>
                @else
                <tr>
                    <td colspan="10" style="text-align: center; color: red;">
                        Error: Item with ID '{{ $item['item'] }}' could not be found. It may have been deleted.
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

        <!-- Quotation Number and Totals -->
        <div class="clearfix">
            <div class="quotation-number">
                <table class="table-auto border border-gray-300">
                    <thead>
                        <tr>
                            <th class="bg-gray-100" style="padding-left: 2.5rem; padding-right: 2.5rem;">QUOTATION NO.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">{{ $record->quotation_number }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="totals-table">
                <table class="table-auto border border-gray-300">
                    <tbody>
                        <tr>
                            <td class="bg-gray-100 text-center">SUB TOTAL</td>
                            <td class="text-right">{{ number_format($record->sub_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="bg-gray-100 text-center">DISCOUNT %</td>
                            <td class="text-right">{{ $record->discount == null ? number_format($discount, 2) : number_format($record->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="bg-gray-100 text-center">VAT @ {{ $vat }}%</td>
                            <td class="text-right">{{ number_format($record->sub_total * ($vat/100), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="bg-gray-100 text-center">SUB TOTAL (INCL)</td>
                            <td class="text-right">{{ number_format($record->sub_total + ($record->sub_total * ($vat/100)) - $discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-semibold bg-gray-100 text-center">GRAND TOTAL</td>
                            <td class="font-semibold text-right">{{ number_format($record->total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
