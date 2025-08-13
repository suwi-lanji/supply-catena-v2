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
<div class="">
    <p class="">TRANSFER ORDER</p>
    <p class="">Transfer Order# {{$record->transfer_order_number}}</p>
</div>
<div class="" style="margin-bottom:50px">
    <p class="">DATE: {{$record->date}}</p>
</div>
<div class="">
    <div style="display:inline-block;width:40%;">
        <p class="">SOURCE WAREHOUSE</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $record->source_warehouse_id)->pluck('name')->first();
        @endphp</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $record->source_warehouse_id)->pluck('country')->first();
        @endphp</p>
    </div>
    <div style="display:inline-block;width:40%;">
        <p class="">DESTINATION WAREHOUSE</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $record->destination_warehouse_id)->pluck('name')->first();
        @endphp</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $record->destination_warehouse_id)->pluck('country')->first();
        @endphp</p>
    </div>
</div>
<div class="table" style="width:100%">
    <table style="width:100%">
        <tr style="background-color: darkgray">
            <th style="text-align:left">#</th>
            <th style="text-align:left">Item</th>
            <th style="text-align:left">Quantity</th>
        </tr>
        @foreach ($record->items as $index => $item)

            <tr>
                <td>{{$index + 1}}</td>
                <td>@php
                    echo \App\Models\Item::where('id', $item['item_name'])->pluck('name')->first();
                @endphp</td>
                <td>{{$item['transfer_quantity']}}</td>
            </tr>
        @endforeach
    </table>
</div>