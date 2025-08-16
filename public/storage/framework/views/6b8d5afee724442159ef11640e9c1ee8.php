<?php
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = base_path() . '/storage/app/public/' . $tenant->logo;
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
<h1>Purchase Backorder Report</h1>
<h4>Date: <?php
    echo \Illuminate\Support\Carbon::now();
?></h4>
<?php if($backorderedItems): ?>
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
            <?php $__currentLoopData = $backorderedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                            $order = \App\Models\PurchaseOrder::where("id", $item["purchase_order_id"])->first();
                        ?>
                <tr style="border-bottom: 1px solid #ccc;">
                    <td style="padding: 5px;"><?php
                        echo \App\Models\Item::where('id', $item["item"])->pluck('name')->first();
                    ?></td>
                    <td>
                        <?php echo e($order->purchase_order_number); ?>

                    </td>
                    <td style="padding: 5px;">
                        
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($a["item"] == $item["item"]): ?>
                                    <?php echo e($a["quantity"]); ?>

                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td style="padding: 5px;">
                        <?php
                            $package = \App\Models\PurchaseReceives::where('id', $item["purchase_receives_id"])->first();
                        ?>
                        <?php $__currentLoopData = $package->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($b["item"] == $item["item"]): ?>
                                    <?php echo e($b["quantity_to_receive"]); ?>

                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td style="padding: 5px;"><?php echo e($item["backorder_quantity"]); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No backordered items found.</p>
<?php endif; ?>
<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/pdf-purchases-backorder.blade.php ENDPATH**/ ?>