<?php
$customer = \App\Models\Customer::where('id', $record->customer_id)->first();
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = base_path() . '/storage/app/public' . str_replace('/content/', '/', $tenant->logo);
?>
<style>
    /* ... all your existing styles remain here ... */
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
                    <td colspan="2" class="text-center">Quotation</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Quotation No.</td>
                    <td><?php echo e($record->quotation_number); ?></td>
                </tr>
                <tr>
                    <td>Quotation Date</td>
                    <td><?php echo e($record->quotation_date); ?></td>
                </tr>
                <tr>

                    <td>Prepared By.</td>
                    <td>
                      <?php
                      echo \App\Models\SalesPerson::where('id', $record->sales_person_id)->pluck('email')->first();
                      ?>
                    </td>
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
                    <th>Weight</th>
                    <th>Lead Time</th>
                    <th>Unit Price</th>
                    <th>Discount</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $record->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    // Find the item, but handle cases where it might not exist
                    $itemModel = \App\Models\Item::find($item['item']);
                ?>

                <?php if($itemModel): ?>
                <tr>
                    <td><?php echo e(Arr::get($item, 'quantity', 0)); ?></td>
                    <td><?php echo e($itemModel->part_number ?? $itemModel->name); ?></td>
                    <td><?php echo e($itemModel->description); ?></td>
                    <td><?php echo e($itemModel->condition); ?></td>
                    <td><?php echo e($item['weight']); ?></td>
                    <td><?php echo e($item['lead_time']); ?></td>
                    <td><?php echo e($item['rate']); ?></td>
                    <td><?php echo e(Arr::get($item, 'discount', 0)); ?>%</td>
                    <td><?php echo e($item['amount']); ?></td>
                </tr>
                <?php else: ?>
                
                
                <tr>
                    <td colspan="9" style="text-align: center; color: red;">
                        Error: Item with ID '<?php echo e($item['item']); ?>' <?php echo e(json_encode($item)); ?>could not be found. It may have been deleted.
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td colspan="5"></td>
                    <?php
                    $totalWeight = array_reduce($record->items, function($carry, $item) {
                        return $carry + $item['weight'];
                    }, 0);
                    ?>
                    <td><b>Total Weight: <?php echo e($totalWeight); ?></b></td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="invoice-columns clearfix">
        <div class="terms-column left">
            <h4>Terms and Conditions</h4>
            <ul>
                <?php $__currentLoopData = $record->terms_and_conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $t_and_c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><small><?php echo e($t_and_c['terms_and_conditions']); ?></small></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <div class="invoice-footer right">
            <?php
            $totalVat = array_reduce($record->items, function($carry, $item) {
                return $carry + Arr::get($item, 'tax', 0);
            }, 0);
            $totalDiscount = array_reduce($record->items, function($carry, $item) {
                return $carry + Arr::get($item, 'discount', 0);
            }, 0);
            $totalDiscount += floatval($record->discount);
            ?>
            <p><b>Sub-total: </b><span class="float-end"><?php echo e($tenant->currency_symbol); ?><?php echo e($record->sub_total); ?></span></p>
            <p><b>Discount (<?php echo e($totalDiscount); ?>%):</b> <span class="fw-normal text-body"><?php echo e($totalDiscount); ?>%</span></p>
            <p><b>VAT (<?php echo e($totalVat); ?>%):</b> <span class="fw-normal text-body"><?php echo e($totalVat); ?>%</span></p>
            <h5><?php echo e($tenant->currency_symbol); ?><?php echo e($record->total); ?>  <?php echo e($tenant->currency_code); ?></h5>
        </div>
    </div>

    <div style="margin-bottom: 50px">
        <?php
        $terms = \App\Models\PaymentTerm::find($record->payment_term_id);
        ?>
        <h3>Payment Term: <?php echo e($terms->name); ?></h3>
        <p>Please make payment by check or bank transfer to the following account:</p>
        <div>
            <p><strong>Account Type:</strong> <?php echo e($terms->account_type); ?></p>
            <p><strong>Bank:</strong> <?php echo e($terms->bank); ?></p>
            <p><strong>A/C Name:</strong> <?php echo e($terms->account_name); ?></p>
            <p><strong>Account No:</strong> <?php echo e($terms->account_number); ?></p>
            <p><strong>Branch:</strong> <?php echo e($terms->branch); ?></p>
            <p><strong>Swift Code:</strong> <?php echo e($terms->swift_code); ?></p>
            <p><strong>Branch No:</strong> <?php echo e($terms->branch_number); ?></p>
        </div>
    </div>
</div>
<?php /**PATH /home/suwilanji/dev/supply-catena/resources/views/pdf-quotation.blade.php ENDPATH**/ ?>