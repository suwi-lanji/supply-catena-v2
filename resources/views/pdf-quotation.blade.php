@php
$customer = \App\Models\Customer::where('id', $record->customer_id)->first();
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = storage_path("app/public/" . str_replace("/content/", "/", $tenant->logo));
$terms = \App\Models\PaymentTerm::find($record->payment_term_id);
$vat = 0;
$discount = 0;

// Calculate totals
foreach ($record->items as $item) {
    $vat += $item["tax"] ?? 0;
    $discount += $item["discount"] ?? 0;
}
@endphp

<style>
    body {
        font-family: "Figtree", sans-serif;
        margin: 0;
        padding: 0;
        font-size: 12px;
        color: #000;
        background-color: #fff;
        line-height: 1.2;
    }
    
    .invoice {
        padding: 20px;
        color: #000;
        background-color: #fff;
    }
    
    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }
    
    .header-left {
        float: left;
        width: 60%;
    }
    
    .header-right {
        float: right;
        width: 38%;
        text-align: left;
    }
    
    .logo-container {
        float: left;
        margin-right: 15px;
    }
    
    .company-info {
        float: left;
    }
    
    .delivery-section {
        margin-bottom: 20px;
    }
    
    .delivery-table {
        float: left;
        width: 60%;
    }
    
    .contact-info {
        float: right;
        width: 38%;
        text-align: left;
        padding-top: 10px;
    }
    
    .quotation-details {
        margin-bottom: 15px;
    }
    
    .items-table {
        margin-bottom: 15px;
    }
    
    .footer-section {
        margin-top: 20px;
    }
    
    .quotation-number {
        float: left;
        width: 30%;
    }
    
    .totals-table {
        float: right;
        width: 68%;
    }
    
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }
    
    th, td {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: left;
        font-size: 11px;
    }
    
    th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }
    
    .bg-gray {
        background-color: #f0f0f0;
    }
    
    .text-center {
        text-align: center;
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-left {
        text-align: left;
    }
    
    .font-bold {
        font-weight: bold;
    }
    
    .company-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .quotation-title {
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 8px;
    }
    
    address {
        font-style: normal;
        line-height: 1.3;
    }
    
    .mb-10 {
        margin-bottom: 10px;
    }
    
    .mb-15 {
        margin-bottom: 15px;
    }
    
    .mb-20 {
        margin-bottom: 20px;
    }
</style>

<div class="invoice">
    <!-- Header Section -->
    <div class="clearfix mb-20">
        <div class="header-left">
            <div class="logo-container">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="Logo" style="width:80px;height:80px"/>
            </div>
            <div class="company-info">
                <div class="company-name">{{ $tenant->portal_name }}</div>
                <address>
                    {{ $tenant->street_1 }}<br/>
                    {{ $tenant->city }}, {{ $tenant->province }}, {{ $tenant->business_location }}
                </address>
            </div>
        </div>
        <div class="header-right">
            <div class="quotation-title">QUOTATION</div>
            <div>
                <strong>Bank:</strong> {{ $terms->bank }}<br/>
                <strong>A/C Name:</strong> {{ $terms->account_name }}<br/>
                <strong>Account No:</strong> {{ $terms->account_number }}<br/>
                <strong>Branch:</strong> {{ $terms->branch }}<br/>
                <strong>Swift Code:</strong> {{ $terms->swift_code }}<br/>
                <strong>Branch No:</strong> {{ $terms->branch_number }}
            </div>
        </div>
    </div>

    <!-- Delivery Address Section -->
    <div class="delivery-section clearfix mb-15">
        <div class="delivery-table">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">DELIVERY ADDRESS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <address>
                                <strong>{{ $customer->company_display_name }}</strong><br/>
                                {{ $customer->billing_street_1 }}, {{ $customer->billing_city }}, {{ $customer->billing_country }}<br/>
                                {{ $customer->billing_city }}<br/>
                                <strong>Phone:</strong> {{ $customer->phone }}
                            </address>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="contact-info">
            <strong>OFFICE CELL:</strong> {{ $tenant->phone }}<br/>
            <strong>EMAIL:</strong> {{ $tenant->email }}
        </div>
    </div>

    <!-- Quotation Details Table -->
    <div class="quotation-details mb-15">
        <table>
            <thead>
                <tr>
                    <th>VALID DATE</th>
                    <th>CLIENT</th>
                    <th>REPORT NO.</th>
                    <th>INCO TERM</th>
                    <th>STOCK IN</th>
                    <th>LEAD TIME</th>
                    <th>PAYMENT TERM</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">
                        {{ date("Y/m/d", strtotime($record->quotation_date)) }} - 
                        {{ $record->expected_shippment_date ? date("Y/m/d", strtotime($record->expected_shippment_date)) : '' }}
                    </td>
                    <td class="text-center">{{ $customer->company_display_name }}</td>
                    <td class="text-center">{{ $record->report_number ?: 'N/A' }}</td>
                    <td class="text-center">{{ $record->inco_term ?: 'N/A' }}</td>
                    <td class="text-center">{{ $record->stock_in ?: 'N/A' }}</td>
                    <td class="text-center">{{ $record->lead_time ?: 'N/A' }}</td>
                    <td class="text-center">{{ $record->payment_time ?: 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Items Table -->
    <div class="items-table mb-15">
        <table>
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>MATERIAL NO.</th>
                    <th>PART NO.</th>
                    <th>DESCRIPTION</th>
                    <th>LEAD TIME</th>
                    <th>QTY</th>
                    <th>UNIT PRICE (EXCL)</th>
                    <th>UNIT PRICE (INCL)</th>
                    <th>DISC %</th>
                    <th>TOTAL PRICE (EXCL)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $index => $item)
                    @php
                        $itemModel = \App\Models\Item::find($item['item']);
                    @endphp
                    @if ($itemModel)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $item['sku'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $itemModel->part_number ?? 'N/A' }}</td>
                            <td class="text-center">{{ $itemModel->description ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['lead_time'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? '0' }}</td>
                            <td class="text-center">{{ number_format($item['rate'], 2) }}</td>
                            <td class="text-center">{{ number_format($item['rate'], 2) }}</td>
                            <td class="text-center">{{ $item['discount'] ?? '0' }}%</td>
                            <td class="text-center">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td colspan="9" class="text-center" style="color: red;">
                                Item data not available
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer Section -->
    <div class="footer-section clearfix">
        <div class="quotation-number">
            <table>
                <thead>
                    <tr>
                        <th>QUOTATION NO.</th>
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
            <table>
                <tbody>
                    <tr>
                        <td class="bg-gray text-center">SUB TOTAL</td>
                        <td class="text-right">{{ number_format($record->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-gray text-center">DISCOUNT %</td>
                        <td class="text-right">{{ $record->discount ? number_format($record->discount, 2) : number_format($discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-gray text-center">VAT @ {{ $vat }}%</td>
                        <td class="text-right">{{ number_format($record->sub_total * ($vat/100), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-gray text-center">SUB TOTAL (INCL)</td>
                        <td class="text-right">{{ number_format($record->sub_total + ($record->sub_total * ($vat/100)) - ($record->discount ?? $discount), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-gray text-center font-bold">GRAND TOTAL</td>
                        <td class="text-right font-bold">{{ number_format($record->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
