<?php

namespace App\Livewire;

use App\Models\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListNotifications extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Notification::query())
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('data.title')
                            ->weight(FontWeight::Bold),
                        TextColumn::make('data.body'),
                        TextColumn::make('created_at')
                            ->since(),
                    ])
                        ->alignment(Alignment::Center),
                    IconColumn::make('read_at')
                        ->boolean()
                        ->getStateUsing(function ($record) {
                            return (bool) $record->read_at;
                        })
                        ->trueIcon('heroicon-o-check-badge')
                        ->falseIcon('heroicon-o-x-mark')
                        ->alignment(Alignment::End),
                ]),
            ])
            ->filters([
                Filter::make('unread_notifications')
                    ->query(function (Builder $query) {
                        $query->whereNull('read_at');
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->url(function ($record) {
                        $notification = auth()->user()->unreadNotifications()->find($record->id);
                        if ($notification) {
                            $notification->markAsRead();
                        }

                        return $record->data['url'];
                    }),
            ])
            ->headerActions([
                Action::make('mark_all_as_read')
                    ->button()
                    ->action(function () {
                        foreach (auth()->user()->unreadNotifications()->get() as $notication) {
                            $notication->markAsRead();
                        }

                    }),
            ])
            ->bulkActions([
            ]);
    }

    public function render(): View
    {
        return view('livewire.list-notifications');
    }
}
