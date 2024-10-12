<?php
namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Actions\Action;
use App\Models\Item;
use App\Models\Bill;
use App\Models\Invoices;

class Dashboard extends BaseDashboard {
    public function getHeading(): string {
        $tenant = Filament::getTenant();

        $unReadNotifications = auth()->user()->unreadNotifications()->count();
        $pendingPayment = Bill::where('team_id', Filament::getTenant()->id)->sum('balance_due');
        $unpaidInvoiceBal = Invoices::where('team_id', Filament::getTenant()->id)->sum('balance_due');
        if($pendingPayment > 0) {
            Notification::make()
            ->title('Pending payment ' . $pendingPayment)
            ->danger()
            ->actions([
                Action::make('view_unpaid_bills')
                ->url(route('filament.dashboard.resources.bills.index', ['tenant' =>Filament::getTenant()->id, 'tableFilters[queryBuilder][rules][O02l][type]' => 'balance_due', 'tableFilters[queryBuilder][rules][O02l][data][operator]' => 'isMax.inverse', 'tableFilters[queryBuilder][rules][O02l][data][settings][number]' => 0]))
            ])
            ->send();
        }

        if($unpaidInvoiceBal > 0) {
            Notification::make()
            ->title('Unpaid invoice balance ' . $unpaidInvoiceBal)
            ->danger()
            ->actions([
                Action::make('view_unpaid_invoices')
                ->url(route('filament.dashboard.resources.invoices.index', ['tenant' =>Filament::getTenant()->id, 'tableFilters[queryBuilder][rules][O02l][type]' => 'balance_due', 'tableFilters[queryBuilder][rules][O02l][data][operator]' => 'isMax.inverse', 'tableFilters[queryBuilder][rules][O02l][data][settings][number]' => 0]))
            ])
            ->send();
        }
        if($unReadNotifications > 0) {
            Notification::make()
            ->title("There are ". $unReadNotifications . " Unread Notifications")
            ->warning()
            ->color('warning')
            ->icon('heroicon-o-bell')
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(route('filament.dashboard.pages.notifications', Filament::getTenant()->id)),
            ])
            ->send();
        }

        $numLowStock = Item::whereColumn('stock_on_hand', '<', 'reorder_level')
        ->whereNotNull('reorder_level')->where('team_id', Filament::getTenant()->id)->count();
        if($numLowStock > 0) {

            Notification::make()
                ->title("Low Stock Items Detected")
                ->danger()
                ->body("There are " . $numLowStock . " Items below reorder level")
                ->actions([
                    Action::make('view_items')
                    ->button()
                    ->color('danger')
                    ->url(route('filament.dashboard.resources.items.index', ['tenant' => Filament::getTenant()->id,'tableFilters[low_stock_items][isActive]' => true]))
                ])
                ->send();

        }

        return $tenant->portal_name . "'s Dashboard";
    }
}
?>
