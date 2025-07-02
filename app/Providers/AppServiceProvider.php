<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Bill;
use App\Models\InventoryAdjustment;
use App\Models\PurchaseReceives;
use App\Models\PurchaseOrder;
use App\Models\PaymentsMade;
use App\Models\Invoices;
use App\Observers\BillObserver;
use App\Observers\InvoicesObserver;
use App\Observers\InventoryAdjustmentObserver;
use App\Observers\PurchaseReceivesObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\PaymentsMadeObserver;
use App\Observers\BranchUserObserver;
use App\Observers\PaymentsReceivedObserver;
use App\Models\PaymentsReceived;
use App\Models\Shipments;
use App\Observers\ShipmentsObserver;
use App\Models\User;
use App\Models\BranchUser;
use App\Models\SalesOrder;
use App\Models\Team;
use App\Models\TransferOrder;
use App\Observers\TransferOrderObserver;
use App\Observers\TeamObserver;
use App\Policies\UserAccessPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\UrlGenerator;
use App\Models\Item;
use App\Observers\ItemObserver;
use App\Observers\SalesOrderObserver;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\View\Components\CustomerInfo;
use Illuminate\Support\Facades\Blade;
use Filament\Notifications\Notification as BaseNotification;
use App\Notifications\TenantNotification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Middleware\AddQueueHeaders;
use App\Http\Middleware\SetTenantContext;
use App\Notifications\Channels\DatabaseChannel;
use \Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Filament\Notifications\Notification as FilamentNotification;

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
        $this->app->instance(BaseDatabaseChannel::class, new DatabaseChannel());
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
