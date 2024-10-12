<x-filament-panels::page>
    @php
        $sales_order = \App\Models\SalesOrder::find($record->sales_order_number);
        $customer = \App\Models\Customer::find($sales_order->customer_id);
        $tenant = \Filament\Facades\Filament::getTenant();
        $fullpath = storage_path('app/public/' . str_replace('/content/', '/', $tenant->logo));
        $vat = 0;
        $sub_total = 0;
    @endphp
    <script src="https://cdn.tailwindcss.com"></script>
    <div class="p-8">
        <!-- Invoice Header -->
        <div class="flex justify-between mb-12">
            <div class="flex flex-row">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="Logo" class="mr-3" style="width:150px;height:150px"/>
                <div class="">
                    <h5 class="font-semibold text-xl">{{ $tenant->portal_name }}</h5>
                    <address class="mt-2 text-sm">
                        <span>{{ $tenant->street_1 }}</span><br/>
                        <span>{{ $tenant->city }}, {{ $tenant->province }}, {{ $tenant->business_location }}</span><br/>
                        
                    </address>
                </div>
            </div>
        </div>

        <!-- Delivery Address Table -->
        <div class="flex flex-row justify-between mb-5">
            <table class="table-auto flex-grow border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th colspan="2" class="bg-gray-100 dark:bg-gray-500 text-center">DELIVERY ADDRESS</th>
                    </tr>
                </thead>    
                <tbody>
                    <tr>
                        <td colspan="2" class="">
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
            <div class="ml-20">
                <p>OFFICE CELL: {{ $tenant->phone }}</p>
                <p>EMAIL: {{ $tenant->email }}</p>
            </div>
        </div>

        <!-- PACKAGE Details Table -->
        <div class="mb-8">
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead class="">
                    <tr class="divide-x">
                        <th class="bg-gray-100 dark:bg-gray-500 ">VALID DATE</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">CLIENT</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">REPORT NO.</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">INCO TERM</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">STOCK IN</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">LEAD TIME</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">PAYMENT TERM</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="divide-x">
                    <td class="text-center">
    {{ date('Y/m/d', strtotime($sales_order->sales_order_date)) }} - 
    {{ $sales_order->expected_shippment_date != null ? date('Y/m/d', strtotime($sales_order->expected_shippment_date)) : '' }}
</td>
                        <td class="text-center">{{ $customer->company_display_name }}</td>
                        <td class="text-center">{{ $sales_order->report_number }}</td>
                        <td class="text-center">{{ $sales_order->inco_term}}</td>
                        <td class="text-center">{{ $sales_order->stock_in }}</td>
                        <td class="text-center">{{ $sales_order->lead_time }}</td>
                        <td class="text-center">{{ $sales_order->payment_time }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Invoice Items Table -->
        <div class="mb-12">
            <table class="table-auto w-full text-sm border-collapse border border-gray-300">
            <thead class="bg-gray-800">
                <tr class="divide-x">
                    <th class="bg-gray-100 dark:bg-gray-500 ">Qty</th>
                    <th class="bg-gray-100 dark:bg-gray-500 ">Part No.</th>
                    <th class="bg-gray-100 dark:bg-gray-500 ">Description</th>
                    <th class="bg-gray-100 dark:bg-gray-500 ">Condition</th>
                    <th class="bg-gray-100 dark:bg-gray-500 ">Unit Price</th>
                    <th class="bg-gray-100 dark:bg-gray-500 ">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $index => $item)
                @php
                $sub_total += $item['amount'];
                $vat += $item['tax'];
                @endphp
                    <tr class="divide-x">
                    <td class="text-center">@php
                            echo Arr::get($item, 'quantity', 0);
                        @endphp</td>
                    <td class="text-center">{{ \App\Models\Item::where('id', $item['item'])->pluck('part_number')->first() }}</td>
                    <td class="text-center">{{ \App\Models\Item::where('id', $item['item'])->pluck('description')->first() }}</td>
                    <td class="text-center">{{ \App\Models\Item::where('id', $item['item'])->pluck('condition')->first() }}</td>
                    <td class="text-center">{{$item['rate']}}</td>
                    <td class="text-center">{{$item['amount']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
            <!-- PACKAGE Number and Totals -->
            <!-- PACKAGE Number and Totals -->
<div class="flex justify-between items-end">
    <table class="table-auto border-collapse border border-gray-300 h-fit">
        <thead>
            <tr>
                <th class="bg-gray-100 dark:bg-gray-500 px-10">PACKAGE NO.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ $record->sales_order_number }}</td>
            </tr>
        </tbody>
    </table>
    <table class="table-auto w-1/2 text-right border-collapse border border-gray-300">
        <tbody class="divide-y">
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">SUB TOTAL</td>
                <td class="text-right">{{ number_format($sub_total, 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">VAT @ {{ $vat }}%</td>
                <td class="text-right">{{ number_format($sub_total * ($vat/100), 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">SUB TOTAL (INCL)</td>
                <td class="text-right">{{ number_format($sub_total + ($sub_total * ($vat/100)), 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class=" font-semibold bg-gray-100 dark:bg-gray-500 text-center">GRAND TOTAL</td>
                <td class=" font-semibold text-right">{{ number_format($sub_total + ($sub_total * ($vat/100)), 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
        </div>
    </div>
</x-filament-panels::page>
