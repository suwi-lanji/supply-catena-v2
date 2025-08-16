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
        $customer = \App\Models\Customer::where('id', $record->customer_id)->first();
        $tenant = \Filament\Facades\Filament::getTenant();
        $fullpath = storage_path('app/public/' . str_replace('/content/', '/', $tenant->logo));
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

        <!--
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
    <?php echo e(date('Y/m/d', strtotime($record->sales_order_date))); ?> - 
    <?php echo e($record->expected_shippment_date != null ? date('Y/m/d', strtotime($record->expected_shippment_date)) : ''); ?>

</td>
                        <td class="text-center"><?php echo e($customer->company_display_name); ?></td>
                        <td class="text-center"><?php echo e($record->report_number); ?></td>
                        <td class="text-center"><?php echo e($record->inco_term); ?></td>
                        <td class="text-center"><?php echo e($record->stock_in); ?></td>
                        <td class="text-center"><?php echo e($record->lead_time); ?></td>
                        <td class="text-center"><?php echo e($record->payment_time); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
--->
        <!-- Invoice Items Table -->
        <div class="mb-12">
            
            <table class="table-auto w-full text-sm border-collapse border border-gray-300">
            <thead class="border-top border-bottom border-light">
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
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $record->packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = \App\Models\Packages::where('id', $pid)->pluck('items')->first(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="divide-x">
                    <td class="text-center"><?php
                            echo Arr::get($item, 'quantity', 0);
                        ?></td>
                    <td class="text-center"><?php echo e(\App\Models\Item::where('id', $item['item'])->get()->map(function($item) { return $item->part_number ?? $item->name; })->first()); ?></td>
                    <td class="text-center"><?php echo e(\App\Models\Item::where('id', $item['item'])->pluck('description')->first()); ?></td>
                    <td class="text-center"><?php echo e(\App\Models\Item::where('id', $item['item'])->pluck('condition')->first()); ?></td>
                    <td class="text-center"><?php echo e($item['rate']); ?></td>
                    <td class="text-center"><?php echo e($item['amount']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->    
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </tbody>
        </table>
            <!-- SHIPMENT Number and Totals -->
            <!-- SHIPMENT Number and Totals -->
<div class="flex justify-between items-end mt-10">
    <table class="table-auto border-collapse border border-gray-300 h-fit">
        <thead>
            <tr>
                <th class="bg-gray-100 dark:bg-gray-500 px-10">SHIPMENT NO.</th>
                <th class="bg-gray-100 dark:bg-gray-500 px-10">SHIPMENT DATE.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center"><?php echo e($record->shipment_order_number); ?></td>
                <td class="text-center"><?php echo e($record->shipment_date); ?></td>
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
<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/filament/resources/shipments/pages/view-shipment.blade.php ENDPATH**/ ?>