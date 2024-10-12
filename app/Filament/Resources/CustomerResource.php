<?php

namespace App\Filament\Resources;
use Filament\Facades\Filament;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationGroup = "Sales";
    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Customer Information')
                    ->schema([
                        Forms\Components\Select::make('customer_type')
                            ->options(['Business', 'Individual'])
                            ->required(),
                        Forms\Components\Select::make('salutation')
                            ->options(['Mr','Mrs', 'Miss', 'Ms', 'Dr'])
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->required(),
                        Forms\Components\TextInput::make('last_name')
                            ->required(),
                        Forms\Components\TextInput::make('company_name')->required(),
                        Forms\Components\TextInput::make('company_display_name')
                            ->label('Display Name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required(),
                        Forms\Components\TextInput::make('tpin')
                        ->required(),
                        Forms\Components\TextInput::make('branch_id')
                        ->label('Brnach ID')
                        ->required(),
                        Forms\Components\Toggle::make('useYn')
                        ->label('Is Active')
                        ->default(true)
                        ->required(),
                        Forms\Components\TextInput::make('regrNm')
                            ->label('Registrant Name')
                            ->required()
                            ->default(auth()->user()->name)
                            ->maxLength(60),
                        Forms\Components\Select::make('regr_id')
                            ->relationship('registrant', 'email')
                            ->searchable()
                            ->default(auth()->user()->id)
                            ->required(),
                        Forms\Components\TextInput::make('modrNm')
                            ->label('Modifier Name')
                            ->default(auth()->user()->name)
                            ->required()
                            ->maxLength(60),
                        Forms\Components\Select::make('modr_id')
                            ->relationship('modifier', 'email')
                            ->default(auth()->user()->id)
                            ->required()
                            ->searchable(),
                    ]),
                Forms\Components\Fieldset::make('Other Details')
                    ->schema([
                        Forms\Components\Select::make('payment_terms')
                            ->relationship('payment_term', 'name')
                            ->createOptionForm([
Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                                Forms\Components\TextInput::make('name'),
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
                            ->required()
                    ]),
                Forms\Components\Fieldset::make('Address')
                    ->schema([
                        Forms\Components\Fieldset::make('Billing Address')
                            ->schema([
                                Forms\Components\TextInput::make('billing_street_1')->required(),
                                Forms\Components\TextInput::make('billing_street_2'),
                                Forms\Components\TextInput::make('billing_city')->required(),
                                Forms\Components\TextInput::make('billing_province')->required(),
                                Forms\Components\TextInput::make('billing_country')->required(),
                                Forms\Components\TextInput::make('billing_phone')->required(),
                            ]),
                        Forms\Components\Fieldset::make('Shipping Address')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_street_1')->required(),
                                Forms\Components\TextInput::make('shipping_street_2')->required(),
                                Forms\Components\TextInput::make('shipping_city')->required(),
                                Forms\Components\TextInput::make('shipping_province')->required(),
                                Forms\Components\TextInput::make('shipping_country')->required(),
                                Forms\Components\TextInput::make('shipping_phone')->required(),
                            ]),
                        ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Textarea::make('remarks')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_display_name')->label('Display Name'),
                Tables\Columns\TextColumn::make('first_name'),
                Tables\Columns\TextColumn::make('last_name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone')
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
