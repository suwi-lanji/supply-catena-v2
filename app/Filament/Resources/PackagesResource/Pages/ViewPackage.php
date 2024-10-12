<?php

namespace App\Filament\Resources\PackagesResource\Pages;
use App\Jobs\ProcessTenantNotification;
use App\Filament\Resources\PackagesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Facades\Filament;
use App\Models\Item;
use App\Models\Invoices;
use App\Models\SalesOrder;
use Filament\Support\Enums\IconPosition;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Forms;
use App\Models\Packages;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Illuminate\Database\Eloquent\Model;
use App\Mail\PackageMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment;
use App\Notifications\DatabaseNotification;
use Spatie\InteractsWithPayload\Facades\AllJobs;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Multitenancy\Tenant;
class ViewPackage extends ViewRecord
{
    protected static string $resource = PackagesResource::class;
    protected static string $view = 'filament.resources.packages.pages.view-package';
    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
            ->action(function($record) {
                Mail::to(Customer::where('id', SalesOrder::where('id', $this->getRecord()->sales_order_number)->pluck('customer_id')->first())->pluck('email')->first())->send(new PackageMail($record=$this->getRecord(), $stream=Pdf::loadView('pdf-package', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'DOMPDF_ENABLE_HTML5PARSER' => true,
                    'chroot' => public_path(),
                    'fontDir' => storage_path('fonts/'),
                    'isRemoteEnabled' => true,
                ])->output(), $filename=$this->getRecord()->package_slip.'.pdf'));

                $recipients = Filament::getTenant()->users;

                foreach($recipients as $recipient) {
                    $recipient->notify(new DatabaseNotification("Package Created", "New Package created", route('filament.dashboard.resources.packages.view', ["tenant" => Filament::getTenant()->id, "record" => $record->id]), Filament::getTenant()->id));
                }
            }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-package', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                        ])->stream();
                    }, $record->package_slip.'.pdf');
                }),
            Actions\ActionGroup::make([
                Actions\Action::make('shippment')
                ->action(function (Model $record) {
                    $package_id = Packages::where('sales_order_number', $record->id)->pluck('id')->first();
                    if($package_id) {
                        return redirect()->route('filament.dashboard.resources.shipments.create', ['tenant'=>Filament::getTenant(), 'package_id' =>$package_id, 'customer_id' => $this->getRecord()->customer_id]);
                    }
                })
                ->disabled(!$this->getRecord()->shipped),
            ])
            ->label('Create')
            ->button()
            ->icon('heroicon-m-chevron-down')
            ->iconPosition(IconPosition::After)
            ->color('default'),
            Actions\ActionGroup::make([
                Actions\Action::make('void'),

                Actions\Action::make('cancel_items')
            ])
            ->color('default')
        ];
    }
}
