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
        $payment_modes = ['Bank Remittance','Bank Transfer','Cash','Check','Credit Card','Other'];
        $fullpath = base_path() . '/storage/app/public/' . $tenant->logo;
        $paid_through = ['Petty Cash','Undeposited funds','Employee Reimbursements','Drawings','Opening Balance Offset','Owners Equity','Employee Advance', 'Other'];
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
    <div class="">
    <div class="invoice-header clearfix">
        <div class="left">
        <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents($fullpath))); ?>" alt="Logo" style="width: 150px; height: 150px;display:inline-block;"/>
        </div>
        <div class="right">
            <h5><?php echo e($tenant->portal_name); ?></h5>
            <address>
                <span><?php echo e($tenant->email); ?></span><br/>
                <span><?php echo e($tenant->street_1); ?></span><br/>
                <span><?php echo e($tenant->city); ?>, <?php echo e($tenant->province); ?>, <?php echo e($tenant->business_location); ?></span><br/>
                <abbr title="Phone">Phone:</abbr> <span><?php echo e($tenant->phone); ?></span>
            </address>
        </div>
    </div>
        <div class="">
            <h1 style="text-align:center;font-size:2rem">
                PAYMENTS MADE
            </h1>
        </div>
        <div class="">
            <p>Payment #: <?php echo e($this->record->payment_number); ?></p>
            <p>Payment Date: <?php echo e($this->record->payment_date); ?></p>
            <p>Reference Number: <?php echo e($this->record->reference_number); ?></p>
            <p>Paid To: <?php
                echo \App\Models\Vendor::where('id', $this->record->vendor_id)->pluck('vendor_display_name')->first();
            ?></p>
            <p>Payment Mode: <?php echo e($payment_modes[$this->record->payment_mode]); ?></p>
            <p>Paid Through: <?php echo e($paid_through[$this->record->paid_through]); ?></p>
            <p>Amount: <?php echo e($tenant->currency_symbol); ?><?php echo e($this->record->payment_made); ?></p>
        </div>
        <div>
            <div class="table" style="width:100%;margin-top:20px">
            <table style="width:100%">
                <tr style="background-color: darkgray">
                    <th style="text-align:left">Bill Number</th>
                    <th style="text-align:left">Bill Date</th>
                    <th style="text-align:left">Bill Amount</th>
                    <th style="text-align:left">Payment Amount</th>
                </tr>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <tr>
                        <td><?php echo e($item['bill_number']); ?></td>
                        <td><?php echo e($item['date']); ?></td>
                        <td><?php echo e($item['bill_amount']); ?></td>
                        <td><?php echo e($item['payment']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
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
<?php endif; ?><?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/filament/resources/payments-mades/pages/view-payments-made.blade.php ENDPATH**/ ?>