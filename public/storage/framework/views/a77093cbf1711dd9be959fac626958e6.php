<?php
$customer = \App\Models\Customer::where('id', $record->customer_id)->first();
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
<div style="font-family: Arial, sans-serif; margin: 20px;font-size: 0.7rem">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents($fullpath))); ?>" alt="" style="width: 150px; height: 150px;display:inline-block;" />
        <div style="display:inline-block;width: 80%;text-align: right;vertical-align:top;">
            <p style="margin: 0; font-weight: bold;"><?php echo e($tenant->portal_name); ?></p>
            <p style="margin: 0;"><?php echo e($tenant->email); ?></p>
            <p style="margin: 0;"><?php echo e($tenant->street_1); ?>, <?php echo e($tenant->city); ?></p>
            <p style="margin: 0;"><?php echo e($tenant->phone); ?></p>
        </div>
    </div>
    <div style="margin-top: 20px;">
        <div class="clearfix">
            <div class="left">
                <p><strong>Name:</strong> <?php echo e($customer['company_display_name']); ?></p>
                <p><strong>Address:</strong> <?php echo e($customer['billing_street_1']); ?>, <?php echo e($customer['billing_city']); ?>, <?php echo e($customer['billing_country']); ?></p>
                <p><strong>Phone:</strong> <?php echo e($customer['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo e($customer['email']); ?></p>
            </div>
            <div class="right">
                <p><strong>Sales Receipt Number:</strong> <?php echo e($record->sales_receipt_number); ?></p>
                <p><strong>Sales Receipt Date:</strong> <?php echo e($record->receipt_date); ?></p>
            </div>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Item</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Quantity</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Unit Price</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $itemDetails = \App\Models\Item::where('id', $item['item'])->first();
                ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo e($itemDetails->name); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo e($item['quantity']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo e($item['rate']); ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo e($item['amount']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="clearfix">
    <div style="margin-top: 20px;" class="right">
        <p><strong>Sub Total:</strong> <?php echo e($record->sub_total); ?></p>
        <?php
            $totalVat = 0.0;
            foreach ($record->items as $item) {
                $totalVat += $item['tax'];
            }
        ?>
        <p><strong>VAT (%):</strong> <?php echo e($totalVat); ?></p>
        <p><strong>Total (<?php echo e($tenant->currency_code); ?>):</strong> <?php echo e($record->total); ?></p>
    </div>

    </div>
</div>
<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/pdf-sales-receipt.blade.php ENDPATH**/ ?>