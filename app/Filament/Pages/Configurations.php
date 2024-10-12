<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Setting;

class Configurations extends Page implements HasForms
{

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Settings';
    protected static string $view = 'filament.pages.configurations';
    

    public ?array $data = [];

    public function mount(): void {
        $this->form->fill(
            $settings = Setting::where('team_id', Filament::getTenant()->id)->get()->toArray()
        );
    }

    public function form(Form $form): Form {

        return $form
                ->schema([
                    Forms\Components\TextInput::make('test')
                ])
                ->statePath('data')
                ->model(Setting::class);
    }
}
