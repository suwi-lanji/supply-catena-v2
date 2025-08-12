<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PurchaseOrderSummary extends ChartWidget
{
    protected static ?string $heading = 'Purchase Order Summary';

    protected static ?int $sort = 2;

    public ?string $filter = 'year';

    protected static ?string $maxHeight = '270px';

    protected static string $color = 'info';

    protected static ?array $options = [
        'scales' => [
            'y' => [
                'ticks' => [
                    'stepSize' => 1,
                ],
            ],
        ],
    ];

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;
        if ($filter == 'year') {
            $data = Trend::query(PurchaseOrder::where('team_id', Filament::getTenant()->id))
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->count();
        } elseif ($filter == 'month') {
            $data = Trend::query(PurchaseOrder::where('team_id', Filament::getTenant()->id))
                ->between(
                    start: now()->subMonth()->startOfMonth(),
                    end: now()->subMonth()->endOfMonth(),
                )
                ->perDay()
                ->count();
        } elseif ($filter == 'week') {
            $data = Trend::query(PurchaseOrder::where('team_id', Filament::getTenant()->id))
                ->between(
                    start: now()->subMonth()->startOfWeek(),
                    end: now()->subMonth()->endOfWeek(),
                )
                ->perDay()
                ->count();
        } else {
            $data = Trend::query(PurchaseOrder::where('team_id', Filament::getTenant()->id))
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Purchase Orders Created',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(function (TrendValue $value) {
                if ($this->filter == 'year') {
                    $date = Carbon::createFromFormat('Y-m', $value->date);
                } elseif ($this->filter == 'month') {
                    $date = Carbon::createFromFormat('Y-m-d', $value->date);
                } elseif ($this->filter == 'week') {
                    $date = Carbon::createFromFormat('Y-m-d', $value->date);
                } else {
                    $date = Carbon::createFromFormat('Y-m-d H:i', $value->date);
                }
                if ($this->filter == 'year') {
                    return substr($date->format('F'), 0, 3);
                } elseif ($this->filter == 'month') {
                    return $date->format('jS');
                } elseif ($this->filter == 'week') {
                    return $date->shortDayName;
                } else {
                    return $date->hour.'H';
                }
            }),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
