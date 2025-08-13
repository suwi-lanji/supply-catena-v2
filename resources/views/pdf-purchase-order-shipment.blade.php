<div class="document">
        @php
    $vendor = \App\Models\Vendor::where('id', $record->vendor_id)->first();
    $fullpath = base_path() . '/storage/app/public/' . $tenant->logo;
@endphp
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

<div style="font-family: Arial, sans-serif;">
    <div style="">
    <div class="invoice-header clearfix">
        <div class="left">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="Logo" style="width: 150px; height: 150px;display:inline-block;"/>
        </div>
        <div class="right">
            <h5>{{ $tenant->portal_name }}</h5>
            <address>
                <span>{{ $tenant->email }}</span><br/>
                <span>{{ $tenant->street_1 }}</span><br/>
                <span>{{ $tenant->city }}, {{ $tenant->province }}, {{ $tenant->business_location }}</span><br/>
                <abbr title="Phone">Phone:</abbr> <span>{{ $tenant->phone }}</span>
            </address>
        </div>
    </div>
    <div class="invoice-tables clearfix">
        <table class="invoice-table left">
            <tbody>
                <tr>
                    <td colspan="2">
                        <address>
                            <span>{{ $vendor['vendor_display_name'] }}</span><br/>
                            <span>{{ $vendor['email'] }}</span><br/>
                            <abbr title="Phone">Phone:</abbr> <span>{{ $vendor['phone'] }}</span>
                        </address>
                    </td>
                </tr>
                <tr>
                    <th class="text-nowrap">Contact No.</th>
                    <td>{{ $vendor['phone'] }}</td>
                </tr>
                <tr>
                    <th class="text-nowrap">Email Address</th>
                    <td>{{ $vendor['email'] }}</td>
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
                    <td colspan="2" class="text-center">Shipment</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Shipment No.</td>
                    <td>{{$record->shipment_order_number}}</td>
                </tr>
                <tr>
                    <td>Order Date</td>
                    <td>{{$record->shipment_date}}</td>
                </tr>
            </tbody>
        </table>
    </div>

        <div style="width: 100%;margin-top:20px">
            <table style="width:100%">
            <tr style="background-color: darkgray; color: #fff;">
                    <th style="text-align: left; padding: 5px;">#</th>
                    <th style="text-align: left; padding: 5px;">Date</th>
                    <th style="text-align: left; padding: 5px;">Ref #</th>
                    <th style="text-align: left; padding: 5px;">Total Amount</th>
                    <th style="text-align: left; padding: 5px;">Expected Delivery Date</th>
                </tr>
                <tbody>
                    @php
                        $purchase_order = \App\Models\PurchaseOrder::where('id', $record->purchase_order_id)->get()->first();
                    @endphp
                    <tr>
                        <td style="padding: 5px;">{{$purchase_order->purchase_order_number}}</td>
                        <td style="padding: 5px;">{{$purchase_order->purchase_order_date}}</td>
                        <td style="padding: 5px;">{{$purchase_order->reference_number}}</td>
                        <td style="padding: 5px;">{{$purchase_order->total}}</td>
                        <td style="padding: 5px;">{{$purchase_order->expected_delivery_date}}</td>
                    </tr>
                </tbody>
                
            </table>
        </div>
    </div>
</div>

        </div>