<?php

namespace App\Filament\Resources\BillResource\Pages;

use Filament\Forms;
use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\VendorCredit;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Blade;
use App\Mail\BillMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Template;
class ViewBillResource extends ViewRecord
{
    protected static string $resource = BillResource::class;
    protected static string $view = 'filament.resources.bills.pages.view-bill';
    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make('edit')->color('success')
            ->color('default'),
            Actions\Action::make('email')->color('default')
            ->form([
                Forms\Components\Select::make('choose_template')
                ->options(Template::where('tenant_id', Filament::getTenant()->id)->pluck('template_name', 'id'))
            ])
            ->action(function ($record, array $data) {
                // Step 1: Fetch the template from the database
                $template = Template::where('id', $data['choose_template'])->first();
                $htmlTemplate = $template->template;
            
                // Step 2: Replace placeholders in the template (example for tenant_address)
                $placeholders = [
                    'tenant_address' => Filament::getTenant()->business_location, // Replace with actual tenant address
                    'team_image' => 'https://via.placeholder.com/150'
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
                $vendorEmail = Vendor::where('id', $this->getRecord()->vendor_id)->pluck('email')->first();
                // Step 4: Generate and stream the PDF for download
                session()->put('pdf_template_html', $htmlTemplate);
                return redirect()->route('generate-pdf');
            
                // The email part is commented out for now; you can uncomment it once you need to send emails:
                
                /* 
                // Step 5: Send email with PDF as attachment
                Mail::to($vendorEmail)->send(new BillMail(
                    $record = $this->getRecord(), // Send the record (bill) data
                    $stream = $pdf,               // Attach the generated PDF
                    $filename = $this->getRecord()->bill_number . '.pdf' // PDF filename
                ));
            
                // Step 6: Send notification after successful email
                Notification::make()
                    ->title('Bill Sent')
                    ->success()
                    ->send();
                */
            }
            ),
            Actions\Action::make('apply_credits')->color('default')
            ->visible(fn() => VendorCredit::where('vendor_id', $this->getRecord()->vendor_id)->exists())
            ->form([
                Forms\Components\Select::make('vendor_credit')
                ->options(VendorCredit::where('vendor_id', $this->getRecord()->vendor_id)->where('amount_due', '>', 0)->pluck('credit_note_number', 'id'))
                ->preload()
->searchable()
                ->live(onBlur: true)
                ->afterStateUpdated(function($get, $set) {
                    $credits = VendorCredit::where('id', $get('vendor_credit'))->pluck('amount_due')->first();
                    $set('available_credits', $credits);
                }),
                Forms\Components\TextInput::make('available_credits')
                ->default(0)
                ->disabled(true),
                Forms\Components\TextInput::make('balance')
                ->default($this->getRecord()->balance_due)
                ->disabled(true),
                Forms\Components\TextInput::make('amount')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($get, $set) {
                    if(floatval($get('amount')) > floatval($get('available_credits'))) {
                        $set('amount', $get('available_credits'));
                    }
                })
                ->numeric()
                ->required()
            ])
            ->action(function (array $data) {
                $credit = VendorCredit::where('vendor_id', $data['vendor_credit'])->get()->first();
                if($credit) {
                    $new_amount_due = floatval($credit['amount_due']) - floatval($data['amount']);
                    $credit->update(['amount_due' => $new_amount_due]);
                    $new_balance_due = $this->getRecord()->balance_due - floatval($data['amount']);
                    $this->getRecord()->update(['balance_due' => $new_balance_due]);
                }
                
            }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-bill', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css'))
                        ])->stream();
                    }, $record->bill_number.'.pdf');
                }),
            Actions\Action::make('record_payment')->color('default')
            ->url(route('filament.dashboard.resources.payments-mades.create', ['tenant'=>Filament::getTenant(), 'bill_id' =>$this->getRecord()->id]))
            ->color('default'),
            Actions\ActionGroup::make([
                Actions\Action::make('void'),
                Actions\Action::make('create_vendor_credits')
                ->url(route('filament.dashboard.resources.vendor-credits.create', ['tenant'=>Filament::getTenant(), 'bill_id' =>$this->getRecord()->id])),
                Actions\Action::make('undo_receive')
            ])
        ];
    }
}
