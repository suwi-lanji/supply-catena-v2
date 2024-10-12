<?php

namespace App\Filament\Resources;
use Filament\Facades\Filament;

use App\Filament\Resources\VendorsResource\Pages;
use App\Filament\Resources\VendorsResource\RelationManagers;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\PaymentTerm;
use Illuminate\Support\Facades\DB;
class VendorsResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = "Purchases";
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('salutation')
                ->options(['Mr','Mrs', 'Miss', 'Ms', 'Dr'])
                ->required(),
                Forms\Components\TextInput::make('first_name'),
                Forms\Components\TextInput::make('last_name'),
                Forms\Components\TextInput::make('company_name'),
                Forms\Components\TextInput::make('vendor_display_name')->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
                Forms\Components\Select::make('payment_terms')
                        ->options(PaymentTerm::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                        ->suffixAction(
                            Actions\Action::make('create_payment_term')
                            ->icon('heroicon-o-plus')
                            ->form([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Fieldset::make('Payment Term Details')
                                ->schema([
                                    Forms\Components\TextInput::make('account_type'),
                                    Forms\Components\TextInput::make('bank'),
                                    Forms\Components\TextInput::make('account_name'),
                                    Forms\Components\TextInput::make('account_number'),
                                    Forms\Components\TextInput::make('branch'),
                                    Forms\Components\TextInput::make('swift_code'),
                                    Forms\Components\TextInput::make('branch_number'),              
                    
                                ])
                            ])
                            ->action(function($data) {
                                $data['team_id'] = Filament::getTenant();
                                $created = PaymentTerm::create($data);

                                return $created;
                            })
                        )
                        ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    
->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    
->searchable(),
                Tables\Columns\TextColumn::make('vendor_display_name')
                    
->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendors::route('/create'),
            'edit' => Pages\EditVendors::route('/{record}/edit'),
        ];
    }
}
