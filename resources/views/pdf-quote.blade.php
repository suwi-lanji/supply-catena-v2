<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - WIMUSANI ENTERPRISE SARL</title>

    <!-- jsPDF and html2canvas Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background-color: #f4f4f4;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .action-button {
            margin: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .container {
            width: 100%;
            max-width: 850px;
            margin: 0 auto 20px auto;
            background-color: #fff;
            padding: 40px;
            text-align: left;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .main-header {
            width: 100%; /* Full width */
            margin-bottom: 20px;
            padding-bottom: 15px;
            overflow: hidden; /* Clear floats */
        }
        .main-header .company-details {
            width: 50%;
            float: left; /* Float to the left */
        }
        .main-header .company-details h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        .main-header .quotation-info {
            width: 50%;
            float: right; /* Float to the right */
            text-align: right;
        }
        .main-header .quotation-info h3 { /* Changed from h2 to h3 */
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .sub-header {
            width: 100%;
            margin-bottom: 20px;
            overflow: hidden; /* Clear floats */
        }
        .delivery-address {
            border: 1px solid #000;
            padding: 10px;
            width: 45%;
            float: left; /* Float to the left */
            line-height: 1.5;
        }
        .contact-info {
            text-align: right;
            width: 50%;
            float: right; /* Float to the right */
        }
        .details-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th, .details-table td, .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .items-table .description {
            text-align: left;
            flex-grow: 1;
        }
        .details-table th, .items-table th {
            background-color: #f2f2f2;
        }
        .footer-section {
            width: 100%;
            overflow: hidden; /* Clear floats */
        }
        .totals-table {
            width: 40%;
            float: right; /* Float to the right */
            border-collapse: collapse;
        }
        .totals-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .totals-table td:first-child {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .quotation-no {
            font-weight: bold;
        }
        .payment-terms-section {
            width: 100%;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #000;
            overflow: hidden;
        }
        .payment-terms-section h4 {
            margin: 0;
            font-size: 16px;
        }
        .payment-terms-section p {
            margin: 5px 0;
            line-height: 1.4;
        }
        .company-logo {
            max-width: 150px;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Action Button -->
    <button class="action-button" onclick="generatePDF()">Download as PDF</button>

    <!-- Quotation Content -->
    <div id="quotationContent" class="container">
        <div class="main-header">
            <div class="company-details">
                <!-- Company Logo with crossOrigin attribute for CORS -->
                <img id="companyLogo" class="company-logo" src="{{asset('storage/' . $tenant->logo)}}" alt="Company Logo" crossOrigin="anonymous" />
            </div>
            <div class="quotation-info">
                <h3>{{ $tenant->portal_name }}</h3>
                <p>
                    {{ $tenant->street_1 }}<br>
                    {{ $tenant->city }}, {{ $tenant->province }}, {{ $tenant->business_location }}<br>
                    Phone: {{ $tenant->phone }}<br>
                </p>
            </div>
        </div>

        <div class="sub-header">
            <div class="delivery-address">
                DELIVERY ADDRESS<br>
                Attention: {{ $customer->company_display_name }}<br>
                {{ $customer->company_display_name }}<br>
                {{ $customer->billing_street_1 }}<br>
                {{ $customer->billing_city }}, {{ $customer->billing_province }}<br>
                {{ $customer->billing_country }}
            </div>
            <div class="contact-info">
                <strong>OFFICE CELL:</strong> {{ $tenant->phone }}<br>
                <strong>EMAIL: {{ $tenant->email }}</strong>
            </div>
        </div>

        <table class="details-table">
            <tr>
                <th>VALID DATE</th>
                <th>CLIENT</th>
                <th>VENDOR NO.</th>
                <th>QUOTATION NO</th>
                <th>INCO TERM</th>
                <th>STOCK IN</th>
                <th>LEAD TIME</th>
                <th>PAYMENT TERM</th>
            </tr>
            <tr>
                <td>{{ $record->quotation_date }} - {{ $record->expected_shippment_date }}</td>
                <td>{{ $customer->company_display_name }}</td>
                <td>{{ $customer->vendor_number }}</td>
                <td>{{ $record->quotation_number }}</td>
                <td>{{ $record->inco_term }}</td>
                <td>{{ $record->stock_in }}</td>
                <td>{{ $record->lead_time }}</td>
                <td>{{ $record->payment_time }} DAYS</td>
            </tr>
        </table>

        <table class="items-table">
            <tr>
                <th>ITEM</th>
                <th style="padding: 0px 5px">PART NO.</th>
                <th>ALTERNATIVE</th>
                <th class="description">DESCRIPTION</th>
                <th>LEAD TIME</th>
                <th>QTY</th>
                <th>UNIT PRICE(EXCL)</th>
                <th>UNIT PRICE(INCL)</th>
                <th>DISC%</th>
                <th>TOTAL PRICE(EXCL)</th>
            </tr>
            @foreach ($record->items as $index => $item)
                    @php
                        $itemModel = \App\Models\Item::find($item['item']);
                    @endphp
                    @if ($itemModel)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $itemModel->part_number ?? '' }}</td>
                            <td class="text-center">{{ $item['alternative'] ?? '' }}</td>
                            <td class="text-center">{{ $itemModel->description ?? '' }}</td>
                            <td class="text-center">{{ $item['lead_time'] ?? '' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? '0' }}</td>
                            <td class="text-center">{{ $tenant->currency_symbol }}{{ number_format($item['rate'], 2) }}</td>
                            <td class="text-center">{{ $tenant->currency_symbol }}{{ number_format($item['rate'] * (1 + ($item['tax'] ?? 0) / 100), 2) }}</td>
                            <td class="text-center">{{ $item['discount'] ?? '0' }}%</td>
                            <td class="text-center">{{ $tenant->currency_symbol }}{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td colspan="9" class="text-center" style="color: red;">
                                Item data not available
                            </td>
                        </tr>
                    @endif
                @endforeach
        </table>
        @php
        $vat = 16;
$discount = 0;

// Calculate totals
foreach ($record->items as $item) {
    $discount += $item["discount"] ?? 0;
}
        @endphp
        <div class="footer-section" style="display: flex; flex-direction: row; align-items: flex-start; justify-content: space-between;">
            <div class="">
                <div class="payment-terms-section">
            <h4>PAYMENT TERMS</h4>
            @php
            $terms = \App\Models\PaymentTerm::find($record->payment_term_id);
            @endphp
            @if($terms)
                <p>Please make payment by check or bank transfer to the following account:</p>
                <div>
                    <p><strong>Account Type:</strong> {{ $terms->account_type }}</p>
                    <p><strong>Bank:</strong> {{ $terms->bank }}</p>
                    <p><strong>A/C Name:</strong> {{ $terms->account_name }}</p>
                    <p><strong>Account No:</strong> {{ $terms->account_number }}</p>
                    <p><strong>Branch:</strong> {{ $terms->branch }}</p>
                    <p><strong>Swift Code:</strong> {{ $terms->swift_code }}</p>
                    <p><strong>Branch No:</strong> {{ $terms->branch_number }}</p>
                </div>
            @else
                <p>Payment terms information not available.</p>
            @endif
        </div>
            </div>
            <div class="">
                <table class="totals-table">
                <tr>
                    <td>SUB TOTAL</td>
                    <td style="text-align:right;">{{ $tenant->currency_symbol }}{{ number_format($record->sub_total, 2) }}</td>
                </tr>
                <tr>
                    <td>VAT @ 16%</td>
                    <td style="text-align:right;">{{ $tenant->currency_symbol }}{{ number_format($record->sub_total * ($vat/100), 2) }}</td>
                </tr>
                <tr>
                    <td>GRAND TOTAL</td>
                    <td style="text-align:right;">{{ $tenant->currency_symbol }}{{ number_format($record->sub_total + ($record->sub_total * ($vat/100)) - ($record->discount ?? $discount), 2) }}</td>
                </tr>
            </table>
            </div>
        </div>
    </div>

    <script>
        // Set up jsPDF
        const { jsPDF } = window.jspdf;

        // Function to preload images and ensure they're ready for PDF generation
        function preloadImages() {
            return new Promise((resolve, reject) => {
                const images = document.querySelectorAll('img');
                let loadedCount = 0;
                const totalImages = images.length;
                
                if (totalImages === 0) {
                    resolve();
                    return;
                }
                
                images.forEach(img => {
                    // Create a new image to preload
                    const preloadImg = new Image();
                    preloadImg.crossOrigin = 'anonymous'; // Handle CORS issues
                    
                    preloadImg.onload = () => {
                        loadedCount++;
                        if (loadedCount === totalImages) {
                            resolve();
                        }
                    };
                    
                    preloadImg.onerror = () => {
                        console.warn('Failed to load image:', img.src);
                        loadedCount++;
                        if (loadedCount === totalImages) {
                            resolve();
                        }
                    };
                    
                    preloadImg.src = img.src;
                });
            });
        }

        // Function to convert images to data URLs
        function convertImagesToDataURLs() {
            return new Promise((resolve) => {
                const images = document.querySelectorAll('img');
                let processedCount = 0;
                const totalImages = images.length;
                
                if (totalImages === 0) {
                    resolve();
                    return;
                }
                
                images.forEach(img => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    
                    // Draw image to canvas
                    ctx.drawImage(img, 0, 0);
                    
                    // Convert to data URL
                    try {
                        const dataURL = canvas.toDataURL('image/png');
                        img.src = dataURL;
                    } catch (e) {
                        console.warn('Could not convert image to data URL:', e);
                    }
                    
                    processedCount++;
                    if (processedCount === totalImages) {
                        resolve();
                    }
                });
            });
        }

        async function generatePDF() {
            const content = document.getElementById('quotationContent');
            const button = document.querySelector('.action-button');
            
            // --- PREPARE FOR CAPTURE ---
            // Store original styles
            const originalBodyBg = document.body.style.backgroundColor;
            const originalContainerShadow = content.style.boxShadow;
            
            // Temporarily change styles for a clean capture
            button.style.display = 'none'; // Hide button
            document.body.style.backgroundColor = 'white'; // Remove gray background
            content.style.boxShadow = 'none'; // Remove container shadow

            try {
                // Preload and convert images
                await preloadImages();
                await convertImagesToDataURLs();
                
                // Give a small delay to ensure all images are properly rendered
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Generate the PDF
                const canvas = await html2canvas(content, {
                    scale: 2, // Improve resolution
                    useCORS: true, // Enable CORS for images
                    allowTaint: true, // Allow tainted canvas
                    logging: false, // Disable logging for cleaner output
                    backgroundColor: '#ffffff' // Ensure white background
                });
                
                // --- RESTORE ORIGINAL STYLES ---
                // Change styles back immediately after capture
                button.style.display = 'block';
                document.body.style.backgroundColor = originalBodyBg;
                content.style.boxShadow = originalContainerShadow;

                // --- CREATE PDF ---
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const canvasWidth = canvas.width;
                const canvasHeight = canvas.height;
                const ratio = canvasWidth / canvasHeight;
                const imgWidth = pdfWidth;
                const imgHeight = pdfWidth / ratio;

                // Check if content fits on one page
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

                // Download the PDF
                pdf.save('{{$record->quotation_number}}.pdf');
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF. Please try again.');
                
                // Restore styles even if there's an error
                button.style.display = 'block';
                document.body.style.backgroundColor = originalBodyBg;
                content.style.boxShadow = originalContainerShadow;
            }
        }
    </script>
</body>
</html>
