<?php

namespace App\Http\Controllers;

use App\Models;
use App\Models\Template;

class DocumentController extends Controller
{
    public function showBill($tenant_id, $document_id, $data_id)
    {
        $template = Template::where('id', $document_id)->first();
        $tenant = Models\Team::find($tenant_id);

        $addr = $tenant->portal_name.'<br/>'.$tenant->email.'<br/>'.$tenant->street_1.'<br/>'.$tenant->city.', '.$tenant->province.', '.$tenant->business_location;
        $htmlTemplate = $template->template;

        $bill = Models\Bill::find(1);

        $data = [
            'bill_number' => $bill->bill_number,
            'order_number' => $bill->order_number,
            'bill_date' => $bill->bill_date,
            'due_date' => $bill->due_date,
            'payment_terms' => 'Hello World',
            'tenant_address' => $addr,
        ];
        foreach ($data as $field => $value) {
            $htmlTemplate = str_replace("__FIELD_{$field}__", nl2br($value), $htmlTemplate);
        }

        $tables = [

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

        return response($htmlTemplate)->header('Content-Type', 'text/html');
    }
}
