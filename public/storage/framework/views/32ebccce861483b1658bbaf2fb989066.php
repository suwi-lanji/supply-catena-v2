
    <?php
        $tenant = \Filament\Facades\Filament::getTenant();
        $payment_modes = ['Bank Remittance','Bank Transfer','Cash','Check','Credit Card','Other'];
        $fullpath = base_path() . '/storage/app/public/' . $tenant->logo;
        $paid_through = ['Petty Cash','Undeposited funds','Employee Reimbursements','Drawings','Opening Balance Offset','Owners Equity','Employee Advance', 'Other'];
    ?>
    <style>
    body {
        font-family: "Figtree", sans-serif;
        margin: 0;
        padding: 0;
        font-size: 0.7rem;
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
                PAYMENTS RECEIVED
            </h1>
        </div>
        <div class="">
            <p>Payment #: <?php echo e($record->payment_number); ?></p>
            <p>Payment Date: <?php echo e($record->payment_date); ?></p>
            <p>Reference Number: <?php echo e($record->reference_number); ?></p>
            <p>Billed To: <?php
                echo \App\Models\Customer::where('id', $record->customer_id)->pluck('company_display_name')->first();
            ?></p>
            <p>Payment Mode: <?php echo e($payment_modes[$record->payment_mode]); ?></p>
            <p>Paid Through: <?php echo e($paid_through[$record->paid_through]); ?></p>
            <p>Amount: <?php echo e($tenant->currency_symbol); ?><?php echo e($record->amount_received); ?></p>
        </div>
        <div>
            <div class="table" style="width:100%;margin-top:20px">
            <table style="width:100%">
                <tr style="background-color: darkgray">
                    <th style="text-align:left">Invoice Number</th>
                    <th style="text-align:left">Invoice Date</th>
                    <th style="text-align:left">Invoice Amount</th>
                    <th style="text-align:left">Payment Amount</th>
                </tr>
                <?php $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <tr>
                        <td><?php echo e($item['invoice_number']); ?></td>
                        <td><?php echo e($item['date']); ?></td>
                        <td><?php echo e($item['invoice_amount']); ?></td>
                        <td><?php echo e($item['payment']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        </div>
        </div>
    </div><?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/pdf-payments-received.blade.php ENDPATH**/ ?>