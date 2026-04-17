<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateConfig
{
    public function getTemplates()
    {
        return
        [
            'bills' => [
                'fields' => [
                    ['name' => 'bill_number', 'type' => 'text_field', 'default' => 'BL-0001'],
                    ['name' => 'order_number', 'type' => 'text_field', 'default' => 'ORN-0001'],
                    ['name' => 'bill_date', 'type' => 'date', 'default' => '2024-04-18'],
                    ['name' => 'due_date', 'type' => 'date', 'default' => '2024-04-30'],
                    ['name' => 'payment_terms', 'type' => 'text_field', 'default' => 'Payment Terms: Zambian Kwacha\nPlease make payment by check or bank transfer to the following account:\nAccount Type: Zambian Kwacha\nBank Name: Zanaco\nAcc Holder Name: Suwilanji Jack Chipofya'], // Changed from relationship to text_field
                    ['name' => 'subject', 'type' => 'text_field', 'default' => 'General'],
                    ['name' => 'discount', 'type' => 'text_field', 'default' => '0'],
                    ['name' => 'notes', 'type' => 'text_field', 'default' => 'N/A'],
                    ['name' => 'items', 'type' => 'table', 'default' => json_encode([
                        'headers' => [
                            ['text' => 'Item', 'colspan' => '1'],
                            ['text' => 'Quantity', 'colspan' => '1'],
                            ['text' => 'Unit Price', 'colspan' => '1'],
                            ['text' => 'Amount', 'colspan' => '1'],
                        ],
                        'rows' => [
                            ['Placeholder Item 1', 1, 0.0, 0.0],
                            ['Placeholder Item 2', 1, 0.0, 0.0],
                        ],
                    ])],
                    ['name' => 'bill_details_table', 'type' => 'table', 'default' => json_encode([
                        'headers' => [
                            [
                                'text' => 'Bill',
                                'colspan' => '2',
                            ],
                        ],
                        'rows' => [
                            [
                                'Bill Number', 'BL-0001',
                            ],
                            [
                                'Bill Date', '2024-06-03',
                            ],
                        ],
                    ])],
                    ['name' => 'adjustment', 'type' => 'key_value', 'default' => '0'],
                    ['name' => 'sub_total', 'type' => 'key_value', 'default' => '0'],
                    ['name' => 'total', 'type' => 'key_value', 'default' => '0'],
                    ['name' => 'balance_due', 'type' => 'key_value', 'default' => '0'],
                    ['name' => 'vendor', 'type' => 'text_field', 'default' => 'Default Vendor'], // Changed from relationship to text_field
                    ['name' => 'team', 'type' => 'text_field', 'default' => 'Default Team'], // Changed from relationship to text_field
                    ['name' => 'team_image', 'type' => 'image', 'default' => 'https://via.placeholder.com/150'],
                    ['name' => 'VAT', 'type' => 'key_value', 'default' => '0%'],
                    ['name' => 'tenant_address', 'type' => 'text_field', 'default' => 'Emerald Web Agency\nWeighbridge\nSolwezi North Western Zambia\nPhone: 0760478215'],
                    ['name' => 'vendor_table', 'type' => 'table', 'default' => json_encode([
                        'headers' => [['text' => 'Monica Chipofya\nmonicaesterchipofya@outlook.com\nPhone: 0766666145', 'colspan' => 2]],
                        'rows' => [
                            [
                                'Contact No.', '0766666145',
                            ],
                            [
                                'Email Address', 'monicaesterchipofya@outlook.com',
                            ],
                            [
                                'VAT NO.', 'N/A',
                            ],
                        ],
                    ])],
                ],
            ],
            // Add other templates here...
        ];
    }
}

class DndTemplateMaker extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = new TemplateConfig;
    }

    public function loadForm(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        $templateName = $request->input('template_name');
        $templates = $this->config->getTemplates();

        // Check if the template name exists in the dictionary
        if (! array_key_exists($templateName, $templates)) {
            return abort(404, 'Template not found');
        }

        // Fetch the corresponding table data for the tenant
        $templateData = DB::table($templateName)->where('tenant_id', $tenantId)->first();

        // Pass the data to the view
        return view('form-builder', [
            'tenant_id' => $tenantId,
            'template_name' => $templateName,
            'template_fields' => $templates[$templateName]['fields'],
            'template_data' => $templateData,
        ]);
    }

    public function saveTemplate(Request $request)
    {
        // Validate the request
        $request->validate([
            'tenant_id' => 'required|integer',
            'template_name' => 'required|string',
            'template' => 'required|string',
        ]);

        // Retrieve the data from the request
        $tenantId = $request->input('tenant_id');
        $templateName = $request->input('template_name');
        $templateContent = $request->input('template');

        // Save the template to the database
        Template::create([
            'tenant_id' => $tenantId,
            'template_name' => $templateName,
            'template' => $templateContent,
        ]);

        return response()->json(['message' => 'Template saved successfully']);
    }
}
