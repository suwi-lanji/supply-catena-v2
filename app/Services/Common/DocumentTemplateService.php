<?php

namespace App\Services\Common;

use App\Models\Template;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class DocumentTemplateService extends BaseService
{
    /**
     * Available document types for templates.
     */
    const DOCUMENT_TYPES = [
        'invoice' => 'Invoice',
        'bill' => 'Bill',
        'quotation' => 'Quotation',
        'sales_order' => 'Sales Order',
        'purchase_order' => 'Purchase Order',
        'credit_note' => 'Credit Note',
        'payment_receipt' => 'Payment Receipt',
        'delivery_note' => 'Delivery Note',
        'shipment' => 'Shipment',
        'transfer_order' => 'Transfer Order',
        'inventory_adjustment' => 'Inventory Adjustment',
    ];

    /**
     * Create a new document template.
     *
     * @param Team $team
     * @param array $data
     * @return Template
     * @throws Exception
     */
    public function createTemplate(Team $team, array $data): Template
    {
        $this->validateTemplateData($data);

        $template = new Template();
        $template->team_id = $team->id;
        $template->name = $data['name'];
        $template->document_type = $data['document_type'];
        $template->description = $data['description'] ?? null;
        $template->is_default = $data['is_default'] ?? false;
        $template->is_active = true;

        // Handle template content
        if (isset($data['template_content'])) {
            $template->template_content = $data['template_content'];
        }

        // Handle template file upload
        if (isset($data['template_file'])) {
            $template->template_path = $this->storeTemplateFile($data['template_file']);
        }

        // Handle jsPDF configuration
        if (isset($data['jspdf_config'])) {
            $template->jspdf_config = json_encode($data['jspdf_config']);
        }

        $template->save();

        // Set as default if needed
        if ($template->is_default) {
            $this->setDefaultTemplate($team, $template);
        }

        $this->logAction('document_template_created', [
            'template_id' => $template->id,
            'name' => $template->name,
            'document_type' => $template->document_type,
        ]);

        return $template;
    }

    /**
     * Update a document template.
     *
     * @param Template $template
     * @param array $data
     * @return Template
     * @throws Exception
     */
    public function updateTemplate(Template $template, array $data): Template
    {
        if (isset($data['name'])) {
            $template->name = $data['name'];
        }
        if (isset($data['description'])) {
            $template->description = $data['description'];
        }
        if (isset($data['document_type'])) {
            $template->document_type = $data['document_type'];
        }
        if (isset($data['template_content'])) {
            $template->template_content = $data['template_content'];
        }
        if (isset($data['template_file'])) {
            // Delete old file if exists
            if ($template->template_path) {
                Storage::delete($template->template_path);
            }
            $template->template_path = $this->storeTemplateFile($data['template_file']);
        }
        if (isset($data['jspdf_config'])) {
            $template->jspdf_config = json_encode($data['jspdf_config']);
        }
        if (isset($data['is_active'])) {
            $template->is_active = $data['is_active'];
        }
        if (isset($data['is_default']) && $data['is_default']) {
            $this->setDefaultTemplate($template->team, $template);
        }

        $template->save();

        $this->logAction('document_template_updated', [
            'template_id' => $template->id,
        ]);

        return $template;
    }

    /**
     * Delete a document template.
     *
     * @param Template $template
     * @return bool
     * @throws Exception
     */
    public function deleteTemplate(Template $template): bool
    {
        if ($template->template_path) {
            Storage::delete($template->template_path);
        }

        $template->delete();

        $this->logAction('document_template_deleted', [
            'template_id' => $template->id,
        ]);

        return true;
    }

    /**
     * Set a template as the default for its document type.
     *
     * @param Team $team
     * @param Template $template
     * @return void
     */
    protected function setDefaultTemplate(Team $team, Template $template): void
    {
        // Unset previous default
        Template::where('team_id', $team->id)
            ->where('document_type', $template->document_type)
            ->where('id', '!=', $template->id)
            ->update(['is_default' => false]);

        $template->is_default = true;
        $template->save();
    }

    /**
     * Get the default template for a document type.
     *
     * @param Team $team
     * @param string $documentType
     * @return Template|null
     */
    public function getDefaultTemplate(Team $team, string $documentType): ?Template
    {
        return Template::where('team_id', $team->id)
            ->where('document_type', $documentType)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all templates for a document type.
     *
     * @param Team $team
     * @param string $documentType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTemplatesForType(Team $team, string $documentType)
    {
        return Template::where('team_id', $team->id)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Generate jsPDF configuration for a document.
     *
     * @param string $documentType
     * @param array $data
     * @return array
     */
    public function generateJsPdfConfig(string $documentType, array $data = []): array
    {
        $defaultConfig = [
            'orientation' => 'portrait',
            'unit' => 'mm',
            'format' => 'a4',
            'compress' => true,
        ];

        // Document-specific configurations
        $configs = [
            'invoice' => [
                'title' => 'Invoice',
                'header' => [
                    'show_logo' => true,
                    'show_company_info' => true,
                ],
                'footer' => [
                    'show_page_numbers' => true,
                    'show_terms' => true,
                ],
            ],
            'bill' => [
                'title' => 'Bill',
                'header' => [
                    'show_logo' => true,
                    'show_company_info' => true,
                ],
            ],
            'quotation' => [
                'title' => 'Quotation',
                'validity' => '30 days',
            ],
            'purchase_order' => [
                'title' => 'Purchase Order',
                'header' => [
                    'show_logo' => true,
                ],
            ],
            'sales_order' => [
                'title' => 'Sales Order',
            ],
            'credit_note' => [
                'title' => 'Credit Note',
            ],
            'delivery_note' => [
                'title' => 'Delivery Note',
                'orientation' => 'portrait',
            ],
            'shipment' => [
                'title' => 'Shipment Note',
            ],
        ];

        $config = array_merge($defaultConfig, $configs[$documentType] ?? [], $data);

        return $config;
    }

    /**
     * Generate JavaScript code for jsPDF document generation.
     *
     * @param Template $template
     * @param array $data
     * @return string
     */
    public function generateJsPdfScript(Template $template, array $data): string
    {
        $config = json_decode($template->jspdf_config ?? '{}', true);
        $config = array_merge($this->generateJsPdfConfig($template->document_type), $config);

        // Generate the JavaScript for client-side PDF generation
        $js = <<<JS
// jsPDF Document Generation
const { jsPDF } = window.jspdf;

const doc = new jsPDF({
    orientation: '{$config['orientation']}',
    unit: '{$config['unit']}',
    format: '{$config['format']}'
});

// Add fonts and styling
doc.setFont('helvetica');

// Header
doc.setFontSize(20);
doc.text('{$config['title']}', 105, 20, { align: 'center' });

// Company info
doc.setFontSize(10);
doc.text('{{company_name}}', 20, 35);
doc.text('{{company_address}}', 20, 40);
doc.text('{{company_phone}}', 20, 45);

// Document info
doc.setFontSize(10);
doc.text('Date: {{document_date}}', 150, 35);
doc.text('Number: {{document_number}}', 150, 40);

// Customer/Vendor info
doc.setFontSize(12);
doc.text('Bill To:', 20, 65);
doc.setFontSize(10);
doc.text('{{customer_name}}', 20, 70);
doc.text('{{customer_address}}', 20, 75);

// Items table
const tableColumn = ['Item', 'Quantity', 'Rate', 'Amount'];
const tableRows = {{items_json}};

doc.autoTable({
    head: [tableColumn],
    body: tableRows,
    startY: 90,
    theme: 'striped',
    headStyles: { fillColor: [66, 139, 202] }
});

// Totals
const finalY = doc.lastAutoTable.finalY + 10;
doc.text('Subtotal: {{subtotal}}', 150, finalY);
doc.text('Tax: {{tax}}', 150, finalY + 5);
doc.setFontSize(12);
doc.text('Total: {{total}}', 150, finalY + 12);

// Footer
doc.setFontSize(8);
doc.text('{{footer_text}}', 105, 280, { align: 'center' });

// Save the PDF
doc.save('{{filename}}.pdf');
JS;

        // Replace placeholders with actual data
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $js = str_replace('{{' . $key . '}}', addslashes($value), $js);
        }

        return $js;
    }

    /**
     * Validate template data.
     *
     * @param array $data
     * @throws Exception
     */
    protected function validateTemplateData(array $data): void
    {
        if (empty($data['name'])) {
            throw new Exception('Template name is required.');
        }

        if (empty($data['document_type'])) {
            throw new Exception('Document type is required.');
        }

        if (!array_key_exists($data['document_type'], self::DOCUMENT_TYPES)) {
            throw new Exception('Invalid document type.');
        }
    }

    /**
     * Store a template file.
     *
     * @param mixed $file
     * @return string
     */
    protected function storeTemplateFile($file): string
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('templates', $filename);
    }

    /**
     * Get all available document types.
     *
     * @return array
     */
    public static function getDocumentTypes(): array
    {
        return self::DOCUMENT_TYPES;
    }
}
