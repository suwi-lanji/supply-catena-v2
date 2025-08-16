<?php
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = base_path() . '/storage/app/public' . str_replace('/content/', '/', $tenant->logo);
?>

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
    <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents($fullpath))); ?>" alt="" style="width: 150px; height: 150px;display:inline-block;" />
    <div style="display:inline-block;width: 80%;text-align: right;vertical-align:top;">
        <p style="margin: 0; font-weight: bold;"><?php echo e($tenant->portal_name); ?></p>
        <p style="margin: 0;"><?php echo e($tenant->email); ?></p>
        <p style="margin: 0;"><?php echo e($tenant->street_1); ?>, <?php echo e($tenant->city); ?></p>
        <p style="margin: 0;"><?php echo e($tenant->phone); ?></p>
    </div>
</div>
<div class="">
    <p class="">TRANSFER ORDER</p>
    <p class="">Transfer Order# <?php echo e($record->transfer_order_number); ?></p>
</div>
<div class="" style="margin-bottom:50px">
    <p class="">DATE: <?php echo e($record->date); ?></p>
</div>
<div class="">
    <div style="display:inline-block;width:40%;">
        <p class="">SOURCE WAREHOUSE</p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $record->source_warehouse_id)->pluck('name')->first();
        ?></p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $record->source_warehouse_id)->pluck('country')->first();
        ?></p>
    </div>
    <div style="display:inline-block;width:40%;">
        <p class="">DESTINATION WAREHOUSE</p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $record->destination_warehouse_id)->pluck('name')->first();
        ?></p>
        <p class=""><?php
            echo \App\Models\Warehouse::where('id', $record->destination_warehouse_id)->pluck('country')->first();
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
        <?php $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <tr>
                <td><?php echo e($index + 1); ?></td>
                <td><?php
                    echo \App\Models\Item::where('id', $item['item_name'])->pluck('name')->first();
                ?></td>
                <td><?php echo e($item['transfer_quantity']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
</div><?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/pdf-transfer-order.blade.php ENDPATH**/ ?>