<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note - {{ $tenant->name ?? 'Company Name' }}</title>

    {{-- 1. JS PDF Libraries --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10pt;
            background-color: #f4f4f4; /* Added back for visual contrast on screen */
            text-align: center;
        }
        /* Style for the download button */
        .action-button {
            margin: 20px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
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
        .company-logo {
            max-width: 150px;
            height: auto;
            display: block;
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

    {{-- 2. Download Button --}}
    <button class="action-button" onclick="generatePDF()">Download Delivery Note PDF</button>

    <div id="deliveryNoteContent" class="container">

        {{-- MAIN DOCUMENT HEADER (Tenant Info) --}}
        <div class="main-header">
            <div class="company-details-block">
                {{-- Assuming $tenant has a logo and address fields --}}
                @if ($tenant->logo ?? false)
                    <img id="companyLogo" class="company-logo" src="{{asset('storage/' . $tenant->logo)}}" alt="Company Logo" crossOrigin="anonymous" />
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
                </div>

                {{-- RECEIVED BY --}}
                <div class="signature-field">
                    <strong>RECEIVED BY:</strong>
                </div>
            </div>

        </div>

        <div class="page-footer">
            <p>PAGE **1 OF 1**</p>
        </div>

    </div>

    {{-- 3. PDF Generation Script --}}
    <script>
        const { jsPDF } = window.jspdf;

        async function generatePDF() {
            const content = document.getElementById('deliveryNoteContent');
            const button = document.querySelector('.action-button');
            
            // Store original styles
            const originalBodyBg = document.body.style.backgroundColor;
            
            // Temporarily change styles for a clean capture
            button.style.display = 'none'; // Hide button
            document.body.style.backgroundColor = 'white'; // Ensure white background for PDF

            // Ensure images are loaded correctly, especially across domains
            const images = content.querySelectorAll('img');
            const imagePromises = Array.from(images).map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise(resolve => {
                    img.onload = resolve;
                    img.onerror = resolve; // Resolve even on error
                });
            });

            await Promise.all(imagePromises);
            
            try {
                const canvas = await html2canvas(content, {
                    scale: 2, // Improve resolution
                    useCORS: true, // Enable CORS
                    allowTaint: true, // Allow tainted canvas for local development
                    logging: false,
                    backgroundColor: '#ffffff'
                });
                
                // Restore original styles
                button.style.display = 'block';
                document.body.style.backgroundColor = originalBodyBg;

                // Create PDF
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const canvasWidth = canvas.width;
                const canvasHeight = canvas.height;
                const ratio = canvasWidth / canvasHeight;
                const imgWidth = pdfWidth;
                const imgHeight = pdfWidth / ratio;

                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pdfHeight;

                // Handle multi-page content
                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pdfHeight;
                }

                // Dynamic filename based on DNote number
                const filename = 'DeliveryNote_' + ('{{ $deliveryNote->dnote_number ?? "N_A" }}') + '.pdf';
                pdf.save(filename);
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF. Please ensure all images are loaded and try again.');
                
                // Restore styles even if there's an error
                button.style.display = 'block';
                document.body.style.backgroundColor = originalBodyBg;
            }
        }
    </script>
</body>
</html>