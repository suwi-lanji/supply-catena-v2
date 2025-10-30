<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note - {{ $tenant->name ?? 'Company Name' }}</title>

    {{-- The PDF functionality (jsPDF/html2canvas) is typically handled by a controller/Livewire component or a separate print view in Laravel. 
        For a purely rendered Blade file, we remove the JS, but I'll keep the styles for the content. --}}

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10pt;
            /* background-color and text-align removed for cleaner print layout */
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 10px;
            background-color: #fff;
            text-align: left;
        }
        .main-header {
            width: 100%;
            margin-bottom: 20px;
            overflow: hidden;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .main-header .company-details-block {
            width: 50%;
            float: left;
            line-height: 1.5;
        }
        .main-header .document-title-block {
            width: 50%;
            float: right;
            text-align: right;
            padding-top: 20px;
        }
        .main-header .document-title-block h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header-box {
            border: 1px solid #000;
            padding: 5px;
            width: 40%;
            margin-bottom: 10px;
        }
        .header-box p {
            margin: 0;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .info-table th, .info-table td {
            text-align: center;
        }
        .item-table th, .item-table td {
             min-height: 20px;
             height: 20px;
        }
        .footer-layout {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
        }
        .dnote-section {
            width: 35%;
        }
        .signature-section {
            width: 60%;
            padding-top: 10px;
            display: flex;
            flex-direction: column; 
            gap: 20px;
        }
        .footer-table {
            width: 100%;
            margin-top: 10px;
        }
        .footer-table th, .footer-table td {
            padding: 3px;
        }
        .signature-field {
            width: 100%;
            text-align: left;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px; 
            width: 50%;
        }
        .page-footer {
            text-align: center;
            margin-top: 20px;
        }
        .align-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <div id="deliveryNoteContent" class="container">

        {{-- MAIN DOCUMENT HEADER (Tenant Info) --}}
        <div class="main-header">
            <div class="company-details-block">
                {{-- Assuming $tenant has a logo and address fields --}}
                @if ($tenant->logo_path ?? false)
                    <img src="{{ asset('storage/' . $tenant->logo_path) }}" alt="Company Logo" style="max-width: 150px; height: auto;">
                @endif
                <p>
                    <strong>{{ strtoupper($tenant->name ?? '') }}</strong><br>
                    {{ $tenant->street_1 ?? '' }} / {{ $tenant->city ?? '' }}<br>
                    {{ $tenant->province ?? '' }}, {{ $tenant->business_location ?? '' }}<br>
                    {{ $tenant->phone ?? '' }}
                </p>
            </div>
            <div class="document-title-block">
                <h1>DELIVERY NOTE</h1>
            </div>
        </div>

        {{-- SHIPPING DETAILS BOX (Customer Info) --}}
        <div class="header-box" style="margin-top: 10px; padding: 10px;">
            <p><strong>SHIP TO:</strong></p>
            <p>{{ $customer->company_display_name ?? '' }}</p>
            <p>{{ $customer->billing_street_1 ?? '' }}</p>
            <p>{{ $customer->billing_city ?? '' }}, {{ $customer->billing_province ?? 'Province' }}</p>
            <p>{{ $customer->billing_country ?? '' }}</p>
        </div>

        {{-- ORDER INFORMATION TABLE --}}
        <table class="info-table">
            <thead>
                <tr>
                    <th style="width: 25%;">CLIENT</th>
                    <th style="width: 15%;">ORDER NO.</th>
                    <th style="width: 15%;">ORDER DATE</th>
                    <th style="width: 20%;">DISPATCH DATE</th>
                    <th style="width: 25%;">MODE OF TRANSPORT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $customer->company_display_name ?? 'N/A' }}</td>
                    {{-- Assuming salesOrder relationship exists on $deliveryNote --}}
                    <td>{{ $deliveryNote->salesOrder->sales_order_number ?? 'N/A' }}</td>
                    <td>{{ $deliveryNote->salesOrder->sales_order_date ?? 'N/A' }}</td>
                    <td>{{ $deliveryNote->created_at->format('d/m/Y') ?? 'N/A' }}</td>
                    <td>{{ $deliveryNote->mode_of_transport ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ITEM DETAILS TABLE --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 5%;">ITEM</th>
                    <th style="width: 10%;">MATERIAL</th>
                    <th style="width: 15%;">PART NO.</th>
                    <th style="width: 40%;">DESCRIPTION</th>
                    <th style="width: 10%;">ORDERED</th>
                    <th style="width: 10%;">DELIVERED</th>
                    <th style="width: 10%;">OUTSTANDING</th>
                </tr>
            </thead>
            <tbody>
                @php $totalWeight = 0; @endphp
                {{-- Iterate over the delivery note items. Assumes 'items' is an array/collection field --}}
                @foreach ($deliveryNote->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        {{-- Assumes fields match the TableRepeater structure --}}
                        <td>{{ $item['material_number'] ?? 'N/A' }}</td>
                        <td>{{ optional(App\Models\Item::find($item['item_id']))->part_number ?? 'N/A' }}</td>
                        <td>{{ $item['description'] ?? 'No Description' }}</td>
                        <td class="align-right">{{ $item['ordered'] ?? 0 }}</td>
                        <td class="align-right">{{ $item['delivered'] ?? 0 }}</td>
                        <td class="align-right">{{ $item['outstanding'] ?? 0 }}</td>
                    </tr>
                    @php 
                        // Hypothetical calculation (replace with your actual weight logic)
                        $totalWeight += ($item['delivered'] ?? 0) * (optional(App\Models\Item::find($item['item_id']))->weight ?? 0);
                    @endphp
                @endforeach

                {{-- Add 1 empty row for spacing if needed and the item list is short (Optional) --}}
                @if (count($deliveryNote->items) < 5)
                    @for ($i = 0; $i < (5 - count($deliveryNote->items)); $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        {{-- FOOTER LAYOUT (DNOTE and Signatures) --}}
        <div class="footer-layout">
            
            <div class="dnote-section">
                {{-- Render the actual calculated weight --}}
                <p><strong>GROSS WEIGHT: {{ number_format($totalWeight, 2) }} KG</strong></p>

                <table class="footer-table">
                    <thead>
                        <tr>
                            <th colspan="2">DNOTE NO.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2">{{ $deliveryNote->dnote_number ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="signature-section">
                {{-- DELIVERED BY --}}
                <div class="signature-field">
                    <strong>DELIVERED BY:</strong>
                    <div class="signature-line"></div>
                </div>

                {{-- RECEIVED BY --}}
                <div class="signature-field">
                    <strong>RECEIVED BY:</strong>
                    <div class="signature-line"></div>
                </div>
            </div>

        </div>

        <div class="page-footer">
            <p>PAGE **1 OF 1**</p>
        </div>

    </div>

</body>
</html>