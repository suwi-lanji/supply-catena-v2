@php
$customer = \App\Models\Customer::where("id", $record->customer_id)->first();
$tenant = \Filament\Facades\Filament::getTenant();
$fullpath = base_path() . "/storage/app/public" . str_replace("/content/", "/", $tenant->logo);
@endphp
@php
        $terms = \App\Models\PaymentTerm::find($record->payment_term_id);
        @endphp
<style>
    body {
    font-family: "Figtree", sans-serif;
    margin: 0;
    padding: 0;
    font-size: 8px; /* Reduced font size for PDF */
}

.invoice {
    box-sizing: border-box;
}

.invoice-header, .invoice-footer {
    margin-bottom: 20px;
}

.invoice-header-left img {
    width: 100px; /* Adjusted for better fit */
    height: 100px;
    border-radius: 50%;
}

.invoice-header-right h5 {
    margin: 0;
    font-size: 10px; /* Reduced size for header text */
}

.invoice-header-right address, .invoice-footer p, .invoice-footer h5 {
    margin: 0;
    font-size: 8px; /* Reduced size for addresses and footer text */
}

.invoice-body {
    margin-bottom: 20px;
}

.clearfix::after {
    content: "";
    display: table;
    clear: both;
}

.left, .right {
    box-sizing: border-box;
    width: 48%;
}

.left {
    float: left;
}

.right {
    float: right;
    text-align: right;
}

.invoice-table {
    width: 100%; /* Adjusted to use full width */
    border-collapse: collapse;
}

.invoice-table th, .invoice-table td {
    border: 1px solid #ccc;
    padding: 2px; /* Reduced padding */
    font-size: 7px; /* Smaller font size for table */
}

.invoice-columns {
    display: flex;
    justify-content: space-between;
}

.terms-column,
.total-column {
    width: 48%;
    padding-top: 10px; /* Reduced padding */
}

ul {
    list-style: none;
    padding: 0;
    font-size: 7px; /* Smaller font size for list items */
}

.table-sm .action-icon {
    font-size: 0.8rem;
}

.table-sm>:not(caption)>*>* {
    padding: 0.2rem;
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
    font-size: 9px; /* Reduced font size for h5 */
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
    text-align: left;
    font-size: 7px; /* Reduced font size for table headers */
}

span, p {
    font-size: 8px; /* Smaller font size for general text */
}


</style>
<div class="invoice">
    <div class="invoice-header clearfix">
        <div class="left">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($fullpath)) }}" alt="Logo" style="width: 100px; height: 100px; display: inline-block;"/>
            <div style="display: inline-block; border: solid 1px; height: 100px; vertical-align: top;">
                <h5>{{ $tenant->portal_name }}</h5>
                <address>
                    <span>{{ $tenant->email }}</span><br/>
                    <span>{{ $tenant->street_1 }}</span><br/>
                    <span>{{ $tenant->city }}, {{ $tenant->province }}, {{ $tenant->business_location }}</span><br/>
                    <abbr title="Phone">Phone:</abbr> <span>{{ $tenant->phone }}</span>
                </address>
            </div>
        </div>
        <div class="right" style="width: 50%; border: solid 1px;">
            <h5>QUOTATION</h5>
            <p><strong>Bank:</strong> {{ $terms->bank }}</p>
            <p><strong>A/C Name:</strong> {{ $terms->account_name }}</p>
            <p><strong>Account No:</strong> {{ $terms->account_number }}</p>
            <p><strong>Branch:</strong> {{ $terms->branch }}</p>
            <p><strong>Swift Code:</strong> {{ $terms->swift_code }}</p>
            <p><strong>Branch No:</strong> {{ $terms->branch_number }}</p>
        </div>
    </div>

    <div class="invoice-tables clearfix">
        <table class="invoice-table left">
            <thead>
                <tr>
                    <th colspan="2">DELIVERY ADDRESS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <address>
                            <span>{{ $customer["company_display_name"] }}</span><br/>
                            <span>{{ $customer["billing_street_1"] }}, {{ $customer["billing_city"] }}, {{ $customer["billing_country"] }}</span><br/>
                            <span>{{ $customer["billing_city"] }}</span><br/>
                            <abbr title="Phone">Phone:</abbr> <span>{{ $customer["phone"] }}</span>
                        </address>
                    </td>
                </tr>
                <tr>
                    <th>Contact No.</th>
                    <td>{{ $customer["phone"] }}</td>
                </tr>
                <tr>
                    <th>Email Address</th>
                    <td>{{ $customer["email"] }}</td>
                </tr>
                <tr>
                    <th>VAT No.</th>
                    <td>N/A</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <table>
            <thead>
                <tr>
                    <th>VALID DATE</th>
                    <th>CLIENT</th>
                    <th>REPORT NO.</th>
                    <th>INCO TERM</th>
                    <th>STOCK IN</th>
                    <th>LEAD TIME</th>
                    <th>PAYMENT TERM</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>01/09/24-22/09/24</td>
                    <td>KCC</td>
                    <td>JOBCARD0007</td>
                    <td>DDP MINSTE</td>
                    <td>EX STOCK</td>
                    <td>20 - 35 WORKING DAYS</td>
                    <td>30 DAYS</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="invoice-body">
        <table class="table table-sm table-centered table-hover table-borderless mb-0 mt-3">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>MATERIAL NO.</th>
                    <th>PART NO.</th>
                    <th>DESCRIPTION</th>
                    <th>LEAD TIME</th>
                    <th>QTY</th>
                    <th>UNIT PRICE (EXCL)</th>
                    <th>UNIT PRICE (INCL)</th>
                    <th>DISC %</th>
                    <th>TOTAL PRICE (EXCL)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Arr::get($item, "sku", 0) }}</td>
                    <td>{{ \App\Models\Item::where("id", $item["item"])->pluck("part_number")->first() }}</td>
                    <td>{{ \App\Models\Item::where("id", $item["item"])->pluck("description")->first() }}</td>
                    <td>{{ $item["lead_time"] }}</td>
                    <td>{{ Arr::get($item, "quantity", 0) }}</td>
                    <td>{{ $item["rate"] }}</td>
                    <td>{{ $item["rate"] }}</td>
                    <td>{{ Arr::get($item, "discount", 0) . "%" }}</td>
                    <td>{{ $item["amount"] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <table class="left">
                <thead>
                    <tr>
                        <th>QUOTATION NO.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>498290844984</td>
                    </tr>
                </tbody>
            </table>
            <table class="right">
                <thead>
                    <tr>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($record->items->sum("amount"), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="invoice-footer">
        <p>{{ $terms->notes }}</p>
    </div>
</div>
