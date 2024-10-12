<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use Filament\Facades\Filament;
use App\Filament\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Warehouse;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;
    protected static string $view = 'filament.resources.warehouse.pages.view-warehouse';
    public function getWidgetData(): array {
        return [
            'warehouse_id' => $this->getRecord()->id
        ];
    }

    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make("edit")->color("default"),
            Actions\Action::make("Create Transfer Oder")
            ->link()
            ->color("danger")
            ->url(route('filament.dashboard.resources.transfer-orders.create', ['tenant'=>Filament::getTenant()])),
            Actions\Action::make("Mark As Primary")->color("success")
            ->link()
            ->visible(fn($record): bool => !$record->is_primary)
            ->action(function($record) {
                Warehouse::where("is_primary", true)->update(["is_primary" => false]);
                $record->update(["is_primary" => true]);
            })
        ];
    }
}
