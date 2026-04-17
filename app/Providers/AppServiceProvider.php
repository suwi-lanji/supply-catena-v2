<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\BranchUser;
use App\Models\InventoryAdjustment;
use App\Models\Invoices;
use App\Models\Item;
use App\Models\PaymentsMade;
use App\Models\PaymentsReceived;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceives;
use App\Models\SalesOrder;
use App\Models\Shipments;
use App\Models\Team;
use App\Models\TransferOrder;
use App\Models\User;
use App\Notifications\Channels\DatabaseChannel;
use App\Notifications\TenantNotification;
use App\Observers\BillObserver;
use App\Observers\BranchUserObserver;
use App\Observers\InventoryAdjustmentObserver;
use App\Observers\InvoicesObserver;
use App\Observers\ItemObserver;
use App\Observers\PaymentsMadeObserver;
use App\Observers\PaymentsReceivedObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\PurchaseReceivesObserver;
use App\Observers\SalesOrderObserver;
use App\Observers\ShipmentsObserver;
use App\Observers\TeamObserver;
use App\Observers\TransferOrderObserver;
use App\Policies\UserAccessPolicy;
use App\View\Components\CustomerInfo;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(User::class, UserAccessPolicy::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        /*FilamentNotification::configureUsing(function (FilamentNotification $notification) {
            return new TenantNotification($notification->id);
        });*/
        $this->app->instance(BaseDatabaseChannel::class, new DatabaseChannel);
        TransferOrder::observe(TransferOrderObserver::class);
        Team::observe(TeamObserver::class);
        Bill::observe(BillObserver::class);
        SalesOrder::observe(SalesOrderObserver::class);
        PurchaseReceives::observe(PurchaseReceivesObserver::class);
        InventoryAdjustment::observe(InventoryAdjustmentObserver::class);
        PaymentsMade::observe(PaymentsMadeObserver::class);
        PaymentsReceived::observe(PaymentsReceivedObserver::class);
        Invoices::observe(InvoicesObserver::class);
        Item::observe(ItemObserver::class);
        BranchUser::observe(BranchUserObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        Shipments::observe(ShipmentsObserver::class);
        if (app()->isProduction()) {
            \URL::forceScheme('https');
            request()->server->set('HTTPS', request()->header('X-Forwarded-Proto', 'https') == 'https' ? 'on' : 'off');
        }
        Blade::component('customer-info', CustomerInfo::class);
    }
}
