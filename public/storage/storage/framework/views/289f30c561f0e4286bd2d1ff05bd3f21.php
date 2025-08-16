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
        $tenant = \Filament\Facades\Filament::getTenant();
    ?>
<div class="">
    <p class="">TRANSFER ORDER</p>
    <p class="">Transfer Order# <?php echo e($this->record->transfer_order_number); ?></p>
</div>
<div class="">
    <p class="">DATE: <?php echo e($this->record->date); ?></p>
</div>
<div class="">
    <div style="display:inline-block;width:40%;">
        <p class="">SOURCE WAREHOUSE</p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $this->record->source_warehouse_id)->pluck('name')->first();
        ?></p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $this->record->source_warehouse_id)->pluck('country')->first();
        ?></p>
    </div>
    <div style="display:inline-block;width:40%;">
        <p class="">DESTINATION WAREHOUSE</p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $this->record->destination_warehouse_id)->pluck('name')->first();
        ?></p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $this->record->destination_warehouse_id)->pluck('country')->first();
        ?></p>
    </div>
</div>
<div class="table" style="width:100%">
    <table style="width:100%">
        <tr style="background-color: darkgray">
            <th style="text-align:left">#</th>
            <th style="text-align:left">Item</th>
            <th style="text-align:left">Quantity</th>
        </tr>
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <tr>
                <td><?php echo e($index + 1); ?></td>
                <td><?php
                    echo \App\Models\Item::where('id', $item['item_name'])->pluck('name')->first();
                ?></td>
                <td><?php echo e($item['transfer_quantity']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </table>
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
<?php endif; ?><?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/filament/resources/transfer-orders/view-transfer-order.blade.php ENDPATH**/ ?>