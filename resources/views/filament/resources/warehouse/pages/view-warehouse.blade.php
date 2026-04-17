<x-filament-panels::page>
    <div class="text-2xl font-bold">{{ $this->getRecord()->name }}</div>
    <div class="mt-10">


        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Item Type
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3">
                            SKU
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Quantity
                        </th>

                    </tr>
                </thead>
                <tbody>
                    @foreach(\Illuminate\Support\Facades\DB::table('warehouse_items')->where('warehouse_id', $this->getRecord()->id)->get() as $item)
                    @php
                    $row = \App\Models\Item::where('id', $item->item_id)->get()->first();
                    @endphp
                    <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{$row->item_type}}
                        </th>
                        <td class="px-6 py-4">
                            {{$row->name}}
                        </td>
                        <td class="px-6 py-4">
                            {{$row->sku}}
                        </td>
                        <td class="px-6 py-4">
                            {{ $item->quantity }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</x-filament-panels::page>
