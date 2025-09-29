<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchUserResource\Pages;
use App\Models\BranchUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchUserResource extends Resource
{
    protected static ?string $model = BranchUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tpin')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bhfId')
                    ->label('Branch Id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('userNm')
                    ->label('Username')
                    ->required()
                    ->maxLength(60),
                Forms\Components\TextInput::make('adrs')
                    ->label('Address')
                    ->required()
                    ->maxLength(200),
                Forms\Components\Toggle::make('useYn')
                    ->label('Is Active')
                    ->required(),
                Forms\Components\TextInput::make('regrNm')
                    ->label('Registrant Name')
                    ->required()
                    ->maxLength(60),
                Forms\Components\Select::make('regr_id')
                    ->relationship('registrant', 'email')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('modrNm')
                    ->label('Modifier Name')
                    ->maxLength(60),
                Forms\Components\Select::make('modr_id')
                    ->relationship('modifier', 'email')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tpin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bhfId')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userNm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('adrs')
                    ->searchable(),
                Tables\Columns\IconColumn::make('useYn')
                    ->boolean(),
                Tables\Columns\TextColumn::make('regrNm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regrId')
                    ->searchable(),
                Tables\Columns\TextColumn::make('modrNm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('modrId')
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_id')
                    ->numeric()
                    ->sortable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBranchUsers::route('/'),
            'create' => Pages\CreateBranchUser::route('/create'),
            'edit' => Pages\EditBranchUser::route('/{record}/edit'),
        ];
    }
}
