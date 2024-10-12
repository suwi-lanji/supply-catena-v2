<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use App\Models\Quotation;
use Spatie\Browsershot\Browsershot;
use Filament\Facades\Filament;
use App\Models\Vendor;
class PDFController extends Controller
{
    public function savePdf(Request $request)
    {
        Log::info('Save PDF request received', ['request' => $request->all()]);

        // Validate the incoming request
        $request->validate([
            'pdfData' => 'required|string'
        ]);

        try {
            // Extract the base64 PDF data from the request
            $pdfData = $request->input('pdfData');

            // Remove the data URL header and decode the base64 string
            $pdfBase64 = str_replace('data:application/pdf;base64,', '', $pdfData);
            $pdfBase64 = str_replace(' ', '+', $pdfBase64);
            $pdfBinary = base64_decode($pdfBase64);

            // Define a file path to save the PDF
            $filePath = 'public/pdf/generated-template-' . time() . '.pdf';

            // Save the binary content to the file
            Storage::put($filePath, $pdfBinary);

            // Log success message
            Log::info('PDF saved successfully', ['file_path' => $filePath]);

            // Return a response with the file URL
            return response()->json([
                'message' => 'PDF saved successfully',
                'path' => Storage::url($filePath),
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving PDF', [
                'error' => $e->getMessage(),
                'session' => session()->all()  // Log all session data
            ]);

            return response()->json([
                'message' => 'Error saving PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generatePdf()
    {
        Log::info('Generate PDF request received');

        try {
            // Log all session variables
            Log::info('Session data', ['session' => session()->all()]);

            // Retrieve HTML content from the session
            $htmlTemplate = session()->get('pdf_template_html', '');
            $record = null;

            // Render Blade template if required
            if (session()->get('template_type') === 'global') {
                if (session()->get('document_type') === 'quotation') {
                    $record = Quotation::find(session()->get('document_id'));
                }
                $htmlTemplate = Blade::render($htmlTemplate, ['record' => $record]);
            } else {
                // Step 2: Replace placeholders in the template (example for tenant_address)
                $placeholders = [
                    'tenant_address' => 'Zambia', // Replace with actual tenant address
                    'team_image' => 'https://via.placeholder.com/150',
                    'business_location' => 'Zambia'
                ];
            
                foreach ($placeholders as $field => $value) {
                    $htmlTemplate = str_replace("__FIELD_{$field}__", nl2br($value), $htmlTemplate);
                }
                $tables = [
                    'vendor_table' => [
                        'headers' => [
                            ['text' => "Monica Chipofya<br/>monicaesterchipofya@outlook.com<br/>Phone: 0766666145", 'colspan' => 2],
                        ],
                        'rows' => [
                            ['Contact No.', '0766666145'],
                            ['Email Address', 'monicaesterchipofya@outlook.com'],
                            ['VAT NO.', 'N/A'],
                        ],
                    ],
                    'bill_details_table' => [
                        'headers' => [
                            ['text' => 'Bill', 'colspan' => 2],
                        ],
                        'rows' => [
                            ['Bill Number', 'BL-0001'],
                            ['Bill Date', '2024-06-03'],
                        ],
                    ],
                    'items' => [
                        'headers' => [
                            ['text' => 'Item', 'colspan' => 1],
                            ['text' => 'Quantity', 'colspan' => 1],
                            ['text' => 'Unit Price', 'colspan' => 1],
                            ['text' => 'Amount', 'colspan' => 1],
                        ],
                        'rows' => [
                            ['Placeholder Item 1', 1, 0, 0],
                            ['Placeholder Item 2', 1, 0, 0],
                        ],
                    ],
                ];
            
                foreach ($tables as $table => $content) {
                    $headerHtml = '';
                    foreach ($content['headers'] as $header) {
                        $headerHtml .= "<th colspan=\"{$header['colspan']}\">{$header['text']}</th>";
                    }
            
                    $rowHtml = '';
                    foreach ($content['rows'] as $row) {
                        $rowHtml .= '<tr>';
                        foreach ($row as $cell) {
                            $rowHtml .= "<td>{$cell}</td>";
                        }
                        $rowHtml .= '</tr>';
                    }
            
                    // Replace placeholders in the template
                    $htmlTemplate = str_replace("__FIELD_{$table}_COLUMNS__", $headerHtml, $htmlTemplate);
                    $htmlTemplate = str_replace("__FIELD_{$table}_ROWS__", $rowHtml, $htmlTemplate);
                }
            
                // Step 3: Get vendor email (for email functionality later)
                $vendorEmail = Vendor::where('id', 1)->pluck('email')->first();
                // Step 4: Generate and stream the PDF for download
            }

            Log::info('HTML template rendered successfully');
            Browsershot::html($htmlTemplate)->save('example.pdf');
            // Return the view with the HTML content
            return response()->json([
                'message' => 'PDF saved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF', [
                'error' => $e->getMessage(),
                'session' => session()->all(), // Log all session data
                'document_type' => session()->get('document_type') // Include additional context if needed
            ]);

            return response()->json([
                'message' => 'Error generating PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
