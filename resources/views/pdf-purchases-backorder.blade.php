@php
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

<div style="display: flex; justify-content: space-between; align-items: center;">
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="" style="width: 150px; height: 150px;display:inline-block;" />
    <div style="display:inline-block;width: 80%;text-align: right;vertical-align:top;">
        <p style="margin: 0; font-weight: bold;">{{ $tenant->portal_name }}</p>
        <p style="margin: 0;">{{ $tenant->email }}</p>
        <p style="margin: 0;">{{ $tenant->street_1 }}, {{ $tenant->city }}</p>
        <p style="margin: 0;">{{ $tenant->phone }}</p>
    </div>
</div>
<h1>Purchase Backorder Report</h1>
<h4>Date: @php
    echo \Illuminate\Support\Carbon::now();
@endphp</h4>
@if ($backorderedItems)
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: darkgray; color: #fff;">
                <th style="padding: 5px; text-align: left;">Item</th>
                <th style="padding: 5px; text-align: left;">Order Number</th>
                <th style="padding: 5px; text-align: left;">Ordered Quantity</th>
                <th style="padding: 5px; text-align: left;">Received Quantity</th>
                <th style="padding: 5px; text-align: left;">Backordered Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($backorderedItems as $item)
            @php
                            $order = \App\Models\PurchaseOrder::where("id", $item["purchase_order_id"])->first();
                        @endphp
                <tr style="border-bottom: 1px solid #ccc;">
                    <td style="padding: 5px;">@php
                        echo \App\Models\Item::where('id', $item["item"])->pluck('name')->first();
                    @endphp</td>
                    <td>
                        {{$order->purchase_order_number}}
                    </td>
                    <td style="padding: 5px;">
                        
                        @foreach ($order->items as $a)
                                @if ($a["item"] == $item["item"])
                                    {{$a["quantity"]}}
                                @endif
                            @endforeach
                    </td>
                    <td style="padding: 5px;">
                        @php
                            $package = \App\Models\PurchaseReceives::where('id', $item["purchase_receives_id"])->first();
                        @endphp
                        @foreach ($package->items as $b)
                                @if ($b["item"] == $item["item"])
                                    {{$b["quantity_to_receive"]}}
                                @endif
                            @endforeach
                    </td>
                    <td style="padding: 5px;">{{ $item["backorder_quantity"] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No backordered items found.</p>
@endif
