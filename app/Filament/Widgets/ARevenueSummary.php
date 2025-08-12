<?php

namespace App\Filament\Widgets;

use App\Models\PaymentsMade;
use App\Models\PaymentsReceived;
use App\Models\SalesOrder;
use App\Models\SalesReturns;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ARevenueSummary extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $returned = 0;
        foreach (SalesReturns::where('team_id', Filament::getTenant()->id)->where('approved', true)->get() as $return) {
            $order = SalesOrder::find($return->sales_order_number);
            if ($order) {
                $returned += floatval($order->total);
            }
        }

        return [
            Stat::make('Total Sales', PaymentsReceived::where('team_id', Filament::getTenant()->id)->sum('amount_received').' '.Filament::getTenant()->currency_code),
            Stat::make('Total Purchases', PaymentsMade::where('team_id', Filament::getTenant()->id)->sum('payment_made').' '.Filament::getTenant()->currency_code),
            Stat::make('Total Returns', $returned.' '.Filament::getTenant()->currency_code),
        ];
    }
}
