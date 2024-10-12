<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class Notifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static string $view = 'filament.pages.notifications';
    public static function getNavigationBadgeColor(): ?string {
        return auth()->user()->unreadNotifications()->count() > 0 ? 'warning' : 'primary';
    }
    public static function getNavigationBadge(): ?string
{
    return auth()->user()->unreadNotifications()->count();
}
}
