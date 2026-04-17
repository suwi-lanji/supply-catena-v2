
@php
        $tenant = \Filament\Facades\Filament::getTenant();
        $fullpath = base_path() . '/storage/app/public/' . $tenant->logo;
        $payment_modes = ['Bank Remittance','Bank Transfer','Cash','Check','Credit Card','Other'];
        $paid_through = ['Petty Cash','Undeposited funds','Employee Reimbursements','Drawings','Opening Balance Offset','Owners Equity','Employee Advance', 'Other'];
    @endphp
    <style>
        body {
            font-size: 0.7rem;
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
    </style>
    <div class="">
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
        <div class="">
            <h1 style="text-align:center;font-size:2rem">
                PAYMENTS MADE
            </h1>
        </div>
        <div class="">
            <p>Payment #: {{$record->payment_number}}</p>
            <p>Payment Date: {{$record->payment_date}}</p>
            <p>Reference Number: {{$record->reference_number}}</p>
            <p>Paid To: @php
                echo \App\Models\Vendor::where('id', $record->vendor_id)->pluck('vendor_display_name')->first();
            @endphp</p>
            <p>Payment Mode: {{$payment_modes[$record->payment_mode]}}</p>
            <p>Paid Through: {{$paid_through[$record->paid_through]}}</p>
            <p>Amount: {{$tenant->currency_symbol}}{{$record->payment_made}}</p>
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
                @foreach ($record->items as $index => $item)

                    <tr>
                        <td>{{$item['bill_number']}}</td>
                        <td>{{$item['date']}}</td>
                        <td>{{$item['bill_amount']}}</td>
                        <td>{{$item['payment']}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        </div>
    </div>