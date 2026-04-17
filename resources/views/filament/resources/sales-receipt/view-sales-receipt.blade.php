<x-filament-panels::page>
    @php
        $customer = \App\Models\Customer::where('id', $record->customer_id)->first();
        $tenant = \Filament\Facades\Filament::getTenant();
        $fullpath = storage_path('app/public/' . str_replace('/content/', '/', $tenant->logo));
        $terms = \App\Models\PaymentTerm::find($record->payment_term_id);
        $vat = 0;
        $discount = 0;
    @endphp
    <script src="https://cdn.tailwindcss.com"></script>
    <div class="p-8">
        <!-- SALES RECEIPT Header -->
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
            <div class="text-left">
                <h5 class="font-semibold text-xl text-center">SALES RECEIPT</h5>
                <p><strong>Bank:</strong> {{ $terms->bank }}</p>
                <p><strong>A/C Name:</strong> {{ $terms->account_name }}</p>
                <p><strong>Account No:</strong> {{ $terms->account_number }}</p>
                <p><strong>Branch:</strong> {{ $terms->branch }}</p>
                <p><strong>Swift Code:</strong> {{ $terms->swift_code }}</p>
                <p><strong>Branch No:</strong> {{ $terms->branch_number }}</p>
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

        <!-- SALES RECEIPT Items Table -->
        <div class="mb-12">
            <table class="table-auto w-full text-sm border-collapse border border-gray-300">
                <thead class="bg-gray-800">
                    <tr class="divide-x">
                        <th class="bg-gray-100 dark:bg-gray-500 ">ITEM</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">MATERIAL NO.</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">PART NO.</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">DESCRIPTION</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">LEAD TIME</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">QTY</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">UNIT PRICE (EXCL)</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">UNIT PRICE (INCL)</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">DISC %</th>
                        <th class="bg-gray-100 dark:bg-gray-500 ">TOTAL PRICE (EXCL)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->items as $index => $item)
                    @php

                    $vat += $item['tax'] ?? 0;
                    $discount += $item['discount'] ?? 0;
                    @endphp
                        <tr class="divide-x">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $item['sku'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ \App\Models\Item::where('id', $item['item'])->value('part_number') ?? 'N/A' }}</td>
                            <td class="text-center">{{ \App\Models\Item::where('id', $item['item'])->value('description') ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['lead_time'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? '0' }}</td>
                            <td class="text-center">{{ number_format($item['rate'], 2) }}</td>
                            <td class="text-center">{{ number_format($item['rate'], 2) }}</td>
                            <td class="text-center">{{ $item['discount'] ?? '0' }}%</td>
                            <td class="text-center">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- SALES RECEIPT Number and Totals -->
            <!-- SALES RECEIPT Number and Totals -->
<div class="flex justify-between items-end">
<table class="table-auto border-collapse divide-y border border-gray-300 h-fit">
        <thead>
            <tr class="divide-x">
                <th class="bg-gray-100 dark:bg-gray-500 px-10">SALES RECEIPT NO.</th>
                <th class="bg-gray-100 dark:bg-gray-500 px-10">SALES RECEIPT DATE.</th>
            </tr>
        </thead>
        <tbody>
            <tr class="divide-x">
                <td class="text-center">{{ $record->sales_receipt_number }}</td>
                <td class="text-center">{{ $record->sales_receipt_date }}</td>
            </tr>
        </tbody>
    </table>
    <table class="table-auto w-1/2 text-right border-collapse border border-gray-300">
        <tbody class="divide-y">
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">SUB TOTAL</td>
                <td class="text-right">{{ number_format($record->sub_total, 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">DISCOUNT %</td>
                <td class="text-right">{{ $record->discount == null ? number_format($discount, 2) : number_format($record->discount, 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">VAT @ {{ $vat }}%</td>
                <td class="text-right">{{ number_format($record->sub_total * ($vat/100), 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">SUB TOTAL (INCL)</td>
                <td class="text-right">{{ number_format($record->sub_total + ($record->sub_total * ($vat/100)) - $discount, 2) }}</td>
            </tr>
            <tr class="text-left">
                <td class=" font-semibold bg-gray-100 dark:bg-gray-500 text-center">GRAND TOTAL</td>
                <td class=" font-semibold text-right">{{ number_format($record->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
        </div>
    </div>
</x-filament-panels::page>
