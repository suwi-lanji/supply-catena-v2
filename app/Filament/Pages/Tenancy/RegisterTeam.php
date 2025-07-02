<?php

namespace App\Filament\Pages\Tenancy;
use App\Models\Team;
use App\Models\Warehouse;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Forms\Components\Fieldset;
class RegisterTeam extends RegisterTenant {
    public static function getLabel(): string {
        return "Register Team";
    }
    public function form(Form $form): Form {
        return $form
            ->schema([
                Fieldset::make('Company Information')
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                             ->disk('cloudinary')
                            ->required(),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('portal_name')
                            ->required(),
                        TextInput::make('industry')
                            ->required()
                            ->columnSpan('full'),
                    ])->columns(1),
                Fieldset::make('Contact Information')
                    ->schema([
                        TextInput::make('business_location')
                            ->required()
                            ->columnSpan('full'),
                        TextInput::make('street_1')
                            ->required(),
                        TextInput::make('street_2'),
                        TextInput::make('city')
                            ->required(),
                        TextInput::make('province')
                            ->required(),
                        TextInput::make('phone')
                            ->required(),
                        TextInput::make('fax'),
                        TextInput::make('email')
                            ->email()
                            ->columnSpan('full'),
                    ]),
                TextInput::make('currency_code')->default('USD'),
                TextInput::make('currency_symbol')->default('$'),
                DatePicker::make('inventory_start')
                    ->required(),
                Select::make('fiscal_year')
                    ->required()
                    ->options([
                        'jan_dec'=> 'January - December',
                        'feb_jan'=> 'February - January',
                        'mar_feb'=> 'March - February',
                        'apr_mar'=> 'April - March',
                        'may_apr' => 'May - April',
                        'jun_may'=> 'June - May',
                        'jul_jun'=> 'July - June',
                        'aug_jul'=> 'August - July',
                        'sep_aug'=> 'September - August',
                        'oct_sep'=> 'October - September',
                        'nov_oct'=> 'November - October',
                        'dec_nov'=> 'December - November',
                    ]),
                TextInput::make('language')
                    ->required(),
            ]);
    }

    public function handleRegistration(array $data): Team {
        $team = Team::create($data);
        $team->members()->attach(auth()->user());
        $team->admins()->attach(auth()->user());
        
        return $team;
    }
}
?>
