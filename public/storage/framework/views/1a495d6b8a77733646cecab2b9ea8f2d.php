<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $sales_order = \App\Models\SalesOrder::find($record->order_number);
        $customer = \App\Models\Customer::where('id', $record->customer_id)->first();
        $tenant = \Filament\Facades\Filament::getTenant();
        $fullpath = storage_path('app/public/' . str_replace('/content/', '/', $tenant->logo));
        $terms = \App\Models\PaymentTerm::find($sales_order->payment_term_id);
        $vat = 0;
        $discount = 0;
    ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <div class="p-8">
        <!-- Invoice Header -->
        <div class="flex justify-between mb-12">
            <div class="flex flex-row">
                <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents($fullpath))); ?>" alt="Logo" class="mr-3" style="width:150px;height:150px"/>
                <div class="">
                    <h5 class="font-semibold text-xl"><?php echo e($tenant->portal_name); ?></h5>
                    <address class="mt-2 text-sm">
                        <span><?php echo e($tenant->street_1); ?></span><br/>
                        <span><?php echo e($tenant->city); ?>, <?php echo e($tenant->province); ?>, <?php echo e($tenant->business_location); ?></span><br/>
                        
                    </address>
                </div>
            </div>
            <div class="text-left">
                <h5 class="font-semibold text-xl text-center">INVOICE</h5>
                <p><strong>Bank:</strong> <?php echo e($terms->bank); ?></p>
                <p><strong>A/C Name:</strong> <?php echo e($terms->account_name); ?></p>
                <p><strong>Account No:</strong> <?php echo e($terms->account_number); ?></p>
                <p><strong>Branch:</strong> <?php echo e($terms->branch); ?></p>
                <p><strong>Swift Code:</strong> <?php echo e($terms->swift_code); ?></p>
                <p><strong>Branch No:</strong> <?php echo e($terms->branch_number); ?></p>
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
                                <span><?php echo e($customer->company_display_name); ?></span><br/>
                                <span><?php echo e($customer->billing_street_1); ?>, <?php echo e($customer->billing_city); ?>, <?php echo e($customer->billing_country); ?></span><br/>
                                <span><?php echo e($customer->billing_city); ?></span><br/>
                                <span>Phone: <?php echo e($customer->phone); ?></span>
                            </address>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="ml-20">
                <p>OFFICE CELL: <?php echo e($tenant->phone); ?></p>
                <p>EMAIL: <?php echo e($tenant->email); ?></p>
            </div>
        </div>

        <!-- INVOICE Details Table -->
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
    <?php echo e(date('Y/m/d', strtotime($record->invoice_date))); ?> - 
    <?php echo e($record->expected_shippment_date != null ? date('Y/m/d', strtotime($record->expected_shippment_date)) : ''); ?>

</td>
                        <td class="text-center"><?php echo e($customer->company_display_name); ?></td>
                        <td class="text-center"><?php echo e($sales_order->report_number); ?></td>
                        <td class="text-center"><?php echo e($sales_order->inco_term); ?></td>
                        <td class="text-center"><?php echo e($sales_order->stock_in); ?></td>
                        <td class="text-center"><?php echo e($sales_order->lead_time); ?></td>
                        <td class="text-center"><?php echo e($sales_order->payment_time); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Invoice Items Table -->
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
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php

                    $vat += $item['tax'] ?? 0;
                    $discount += $item['discount'] ?? 0;
                    ?>
                        <tr class="divide-x">
                            <td class="text-center"><?php echo e($index + 1); ?></td>
                            <td class="text-center"><?php echo e($item['sku'] ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo e(\App\Models\Item::where('id', $item['item'])->value('part_number') ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo e(\App\Models\Item::where('id', $item['item'])->value('description') ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo e($item['lead_time'] ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo e($item['quantity'] ?? '0'); ?></td>
                            <td class="text-center"><?php echo e(number_format($item['rate'], 2)); ?></td>
                            <td class="text-center"><?php echo e(number_format($item['rate'], 2)); ?></td>
                            <td class="text-center"><?php echo e($item['discount'] ?? '0'); ?>%</td>
                            <td class="text-center"><?php echo e(number_format($item['amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>

            <!-- INVOICE Number and Totals -->
            <!-- INVOICE Number and Totals -->
<div class="flex justify-between items-end">
<table class="table-auto border-collapse divide-y border border-gray-300 h-fit">
        <thead>
            <tr class="divide-x">
                <th class="bg-gray-100 dark:bg-gray-500 px-10">INVOICE NO.</th>
                <th class="bg-gray-100 dark:bg-gray-500 px-10">INVOICE DATE.</th>
            </tr>
        </thead>
        <tbody>
            <tr class="divide-x">
                <td class="text-center"><?php echo e($record->invoice_number); ?></td>
                <td class="text-center"><?php echo e($record->invoice_date); ?></td>
            </tr>
        </tbody>
    </table>
    <table class="table-auto w-1/2 text-right border-collapse border border-gray-300">
        <tbody class="divide-y">
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">SUB TOTAL</td>
                <td class="text-right"><?php echo e(number_format($record->sub_total, 2)); ?></td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">DISCOUNT %</td>
                <td class="text-right"><?php echo e($record->discount == null ? number_format($discount, 2) : number_format($record->discount, 2)); ?></td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">VAT @ <?php echo e($vat); ?>%</td>
                <td class="text-right"><?php echo e(number_format($record->sub_total * ($vat/100), 2)); ?></td>
            </tr>
            <tr class="text-left">
                <td class="bg-gray-100 dark:bg-gray-500 text-center">SUB TOTAL (INCL)</td>
                <td class="text-right"><?php echo e(number_format($record->sub_total + ($record->sub_total * ($vat/100)) - $discount, 2)); ?></td>
            </tr>
            <tr class="text-left">
                <td class=" font-semibold bg-gray-100 dark:bg-gray-500 text-center">GRAND TOTAL</td>
                <td class=" font-semibold text-right"><?php echo e(number_format($record->total, 2)); ?></td>
            </tr>
        </tbody>
    </table>
</div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/filament/resources/invoices/pages/view-invoice.blade.php ENDPATH**/ ?>