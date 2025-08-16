<?php
$customer_id = \App\Models\SalesOrder::where('id', $record->sales_order_number)->pluck('customer_id')->first();
$customer = \App\Models\Customer::where('id', $customer_id)->first();
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = base_path() . '/storage/app/public' . str_replace('/content/', '/', $tenant->logo);
?>
<style>
    body {
        font-family: "Figtree", sans-serif;
        margin: 0;
        padding: 0;
        font-size: 0.7em;
    }

    .invoice {
        
        box-sizing: border-box;
    }

    .invoice-header, .invoice-footer {
        margin-bottom: 50px;
    }

    .invoice-header-left img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
    }

    .invoice-header-right h5 {
        margin: 0;
    }

    .invoice-header-right address, .invoice-footer p, .invoice-footer h5 {
        margin: 0;
    }

    .invoice-body {
        margin-bottom: 50px;
    }
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
    .invoice-table {
        width: 48%;
        border-collapse: collapse;
    }

    .invoice-table th, .invoice-table td {
        border: 1px solid #ccc;
        padding: 3px;
        text-align: left;
    }

    .invoice-columns {
        display: flex;
        justify-content: space-between;
    }

    .terms-column,
    .total-column {
        width: 48%;
        padding-top: 70px;padding-bottom: 10px;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    .table-sm .action-icon {
        font-size: 1rem;
    }

    .table-sm>:not(caption)>*>* {
        padding: .2rem .2rem;
    }

    .bg-light-subtle {
        background-color: #fcfcfd !important;
    }

    .border-light {
        --ct-border-opacity: 1;
        border-color: rgba(242, 242, 247, 1) !important;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .mt-3 {
        margin-top: 1.5rem !important;
    }

    .table-dark {
        color: #fff;
        background-color: #212529;
        border-color: #373b3e;
    }

    .h5, h5 {
        font-size: .91rem;
    }

    .table-centered td, .table-centered th {
        vertical-align: middle !important;
    }

    .table-borderless>:not(caption)>*>* {
        border-bottom-width: 0;
    }

    tbody, td, tfoot, th, thead, tr {
        border-color: inherit;
        border-style: solid;
        border-width: 0;
    }

    table {
        border-collapse: collapse;
    }

    .table {
        width: 100%;
        margin-bottom: 1.5rem;
        color: var(--ct-table-color);
        vertical-align: top;
        border-color: var(--ct-table-border-color);
    }
    th {
      text-align:left;
    }
</style>
<div class="invoice">
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

    <div class="invoice-tables clearfix">
        <table class="invoice-table left">
            <tbody>
                <tr>
                    <th class="text-nowrap">TPIN/Account No.</th>
                    <td><?php echo e($customer['tpin']); ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <address>
                            <span><?php echo e($customer['company_display_name']); ?></span><br/>
                            <span><?php echo e($customer['billing_street_1']); ?>, <?php echo e($customer['billing_city']); ?>, <?php echo e($customer['billing_country']); ?></span><br/>
                            <span><?php echo e($customer['billing_city']); ?></span><br/>
                            <abbr title="Phone">Phone:</abbr> <span><?php echo e($customer['phone']); ?></span>
                        </address>
                    </td>
                </tr>
                <tr>
                    <th class="text-nowrap">Contact No.</th>
                    <td><?php echo e($customer['phone']); ?></td>
                </tr>
                <tr>
                    <th class="text-nowrap">Email Address</th>
                    <td><?php echo e($customer['email']); ?></td>
                </tr>
                <tr>
                    <th class="text-nowrap">VAT No.</th>
                    <td>N/A</td>
                </tr>
            </tbody>
        </table>
        <table class="invoice-table right">
            <thead class="table-dark">
                <tr>
                    <td colspan="2" class="text-center">Package</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Package No.</td>
                    <td><?php echo e($record->package_slip); ?></td>
                </tr>
                <tr>
                    <td>Package Date</td>
                    <td><?php echo e($record->date); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="invoice-body">
        <table class="table table-sm table-centered table-hover table-borderless mb-0 mt-3">
            <thead class="border-top border-bottom border-light">
                <tr>
                    <th>Qty</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Condition</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php
                            echo Arr::get($item, 'quantity', 0);
                        ?></td>
                    <td><?php echo e(\App\Models\Item::where('id', $item['item'])->get()->map(function($item) { return $item->part_number ?? $item->name; })->first()); ?></td>
                    <td><?php echo e(\App\Models\Item::where('id', $item['item'])->pluck('description')->first()); ?></td>
                    <td><?php echo e(\App\Models\Item::where('id', $item['item'])->pluck('condition')->first()); ?></td>
                    <td><?php echo e($item['rate']); ?></td>
                    <td><?php echo e($item['amount']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div><?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/pdf-package.blade.php ENDPATH**/ ?>