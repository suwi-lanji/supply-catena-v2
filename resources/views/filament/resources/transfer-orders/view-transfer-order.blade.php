<x-filament-panels::page>
    @php
        $tenant = \Filament\Facades\Filament::getTenant();
    @endphp
<div class="">
    <p class="">TRANSFER ORDER</p>
    <p class="">Transfer Order# {{$this->record->transfer_order_number}}</p>
</div>
<div class="">
    <p class="">DATE: {{$this->record->date}}</p>
</div>
<div class="">
    <div style="display:inline-block;width:40%;">
        <p class="">SOURCE WAREHOUSE</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $this->record->source_warehouse_id)->pluck('name')->first();
        @endphp</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $this->record->source_warehouse_id)->pluck('country')->first();
        @endphp</p>
    </div>
    <div style="display:inline-block;width:40%;">
        <p class="">DESTINATION WAREHOUSE</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $this->record->destination_warehouse_id)->pluck('name')->first();
        @endphp</p>
        <p class="">@php
            echo \App\Models\Warehouse::where('id', $this->record->destination_warehouse_id)->pluck('country')->first();
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
        @foreach ($this->record->items as $index => $item)

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
</x-filament-panels::page>