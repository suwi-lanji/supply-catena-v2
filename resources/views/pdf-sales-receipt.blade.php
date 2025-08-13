@php
$customer = \App\Models\Customer::where('id', $record->customer_id)->first();
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = base_path() . '/storage/app/public/' . $tenant->logo;
@endphp
<style>
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .left, .right {
            box-sizing: border-box;
        }
        .left {
            float: left;
            width: 45%;
        }
        .right {
            float: right;
            width: 45%;
            text-align: right;
        }
    </style>
<div style="font-family: Arial, sans-serif; margin: 20px;font-size: 0.7rem">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="" style="width: 150px; height: 150px;display:inline-block;" />
        <div style="display:inline-block;width: 80%;text-align: right;vertical-align:top;">
            <p style="margin: 0; font-weight: bold;">{{ $tenant->portal_name }}</p>
            <p style="margin: 0;">{{ $tenant->email }}</p>
            <p style="margin: 0;">{{ $tenant->street_1 }}, {{ $tenant->city }}</p>
            <p style="margin: 0;">{{ $tenant->phone }}</p>
        </div>
    </div>
    <div style="margin-top: 20px;">
        <div class="clearfix">
            <div class="left">
                <p><strong>Name:</strong> {{ $customer['company_display_name'] }}</p>
                <p><strong>Address:</strong> {{ $customer['billing_street_1'] }}, {{ $customer['billing_city'] }}, {{ $customer['billing_country'] }}</p>
                <p><strong>Phone:</strong> {{ $customer['phone'] }}</p>
                <p><strong>Email:</strong> {{ $customer['email'] }}</p>
            </div>
            <div class="right">
                <p><strong>Sales Receipt Number:</strong> {{ $record->sales_receipt_number }}</p>
                <p><strong>Sales Receipt Date:</strong> {{ $record->receipt_date }}</p>
            </div>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Item</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Quantity</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Unit Price</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $item)
                @php
                    $itemDetails = \App\Models\Item::where('id', $item['item'])->first();
                @endphp
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $itemDetails->name }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $item['quantity'] }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $item['rate'] }}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $item['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
    <div style="margin-top: 20px;" class="right">
        <p><strong>Sub Total:</strong> {{ $record->sub_total }}</p>
        @php
            $totalVat = 0.0;
            foreach ($record->items as $item) {
                $totalVat += $item['tax'];
            }
        @endphp
        <p><strong>VAT (%):</strong> {{ $totalVat }}</p>
        <p><strong>Total ({{ $tenant->currency_code}}):</strong> {{ $record->total }}</p>
    </div>

    </div>
</div>
