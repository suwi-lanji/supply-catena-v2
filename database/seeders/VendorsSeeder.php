<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Vendor;
use App\Models\Brand;
use App\Models\Manufucturer;
use Illuminate\Database\Seeder;

class VendorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Team::where('name', 'Copperbelt Mining Supplies Ltd')->first();

        if (!$company) {
            $this->command->error('Company not found. Run DemoCompanySeeder first.');
            return;
        }

        // Create manufacturers first
        $this->createManufacturers($company->id);

        // Create brands
        $this->createBrands($company->id);

        // Create vendors
        $vendors = $this->getVendors();

        foreach ($vendors as $vendor) {
            Vendor::create([
                'team_id' => $company->id,
                'salutation' => $vendor['salutation'] ?? null,
                'first_name' => $vendor['first_name'] ?? null,
                'last_name' => $vendor['last_name'] ?? null,
                'company_name' => $vendor['company_name'],
                'vendor_display_name' => $vendor['vendor_display_name'],
                'email' => $vendor['email'],
                'phone' => $vendor['phone'],
                'payment_terms' => $vendor['payment_terms'],
            ]);
        }

        $this->command->info('Vendors created: ' . count($vendors) . ' mining industry suppliers');
    }

    protected function createManufacturers(int $teamId): void
    {
        $manufacturers = [
            'Sandvik AB',
            'Atlas Copco',
            'Caterpillar Inc.',
            'Komatsu Ltd',
            'Liebherr Group',
            'Epiroc AB',
            'Boart Longyear',
            'Joy Global',
            'FLSmidth',
            'Metso Outotec',
            'Weir Group',
            'FLSmidth',
            'Osborn Engineering',
            'Tenova Takraf',
            'thyssenkrupp Industrial Solutions',
            'South African Breweries',
            'Dunlop Industrial',
            'Bridgestone Mining',
            'Hyster-Yale Group',
            'Toyota Industrial Equipment',
        ];

        foreach ($manufacturers as $name) {
            Manufucturer::create([
                'name' => $name,
                'team_id' => $teamId,
            ]);
        }

        $this->command->info('Manufacturers created: ' . count($manufacturers));
    }

    protected function createBrands(int $teamId): void
    {
        $brands = [
            'Sandvik',
            'Atlas Copco',
            'CAT',
            'Komatsu',
            'Liebherr',
            'Epiroc',
            'Boart Longyear',
            'Joy',
            'FLSmidth',
            'Metso',
            'Weir',
            'Warman',
            'Osborn',
            'Tenova',
            'thyssenkrupp',
            'Dunlop',
            'Bridgestone',
            'Hyster',
            'Toyota',
            'Yale',
            'Miller',
            'Lincoln',
            'Esab',
            '3M',
            'Honeywell',
            'Drager',
            'MSA Safety',
            'Ansell',
            'Uvex',
            'Delta Plus',
        ];

        foreach ($brands as $name) {
            Brand::create([
                'name' => $name,
                'team_id' => $teamId,
            ]);
        }

        $this->command->info('Brands created: ' . count($brands));
    }

    protected function getVendors(): array
    {
        return [
            // ==================== HEAVY EQUIPMENT SUPPLIERS ====================
            [
                'company_name' => 'Sandvik Mining and Construction Zambia Ltd',
                'vendor_display_name' => 'Sandvik Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Henrik',
                'last_name' => 'Johansson',
                'email' => 'zambia@sandvik.com',
                'phone' => '+260 211 234 567',
                'payment_terms' => 'Net 45',
            ],
            [
                'company_name' => 'Atlas Copco Zambia Ltd',
                'vendor_display_name' => 'Atlas Copco Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Anders',
                'last_name' => 'Lindqvist',
                'email' => 'zambia@atlascopco.com',
                'phone' => '+260 211 345 678',
                'payment_terms' => 'Net 45',
            ],
            [
                'company_name' => 'CAT Equipment Zambia Ltd',
                'vendor_display_name' => 'CAT Equipment Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Thomas',
                'last_name' => 'Mwamba',
                'email' => 'sales@catequipment.co.zm',
                'phone' => '+260 211 456 789',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Barloworld Equipment Zambia Ltd',
                'vendor_display_name' => 'Barloworld Equipment',
                'salutation' => 'Mr',
                'first_name' => 'Johan',
                'last_name' => 'Van Der Merwe',
                'email' => 'zambia@barloworld.com',
                'phone' => '+260 211 567 890',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Komatsu Zambia Ltd',
                'vendor_display_name' => 'Komatsu Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Takeshi',
                'last_name' => 'Yamamoto',
                'email' => 'zambia@komatsu.com',
                'phone' => '+260 211 678 901',
                'payment_terms' => 'Net 45',
            ],

            // ==================== DRILLING EQUIPMENT ====================
            [
                'company_name' => 'Epiroc Zambia Ltd',
                'vendor_display_name' => 'Epiroc Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Erik',
                'last_name' => 'Nilsson',
                'email' => 'zambia@epiroc.com',
                'phone' => '+260 211 789 012',
                'payment_terms' => 'Net 45',
            ],
            [
                'company_name' => 'Boart Longyear Zambia Ltd',
                'vendor_display_name' => 'Boart Longyear',
                'salutation' => 'Mr',
                'first_name' => 'David',
                'last_name' => 'Thompson',
                'email' => 'zambia@boartlongyear.com',
                'phone' => '+260 211 890 123',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Master Drilling Zambia Ltd',
                'vendor_display_name' => 'Master Drilling',
                'salutation' => 'Mr',
                'first_name' => 'Koos',
                'last_name' => 'Bezuidenhout',
                'email' => 'zambia@masterdrilling.com',
                'phone' => '+260 211 901 234',
                'payment_terms' => 'Net 30',
            ],

            // ==================== PROCESSING EQUIPMENT ====================
            [
                'company_name' => 'FLSmidth Zambia Ltd',
                'vendor_display_name' => 'FLSmidth Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Lars',
                'last_name' => 'Pedersen',
                'email' => 'zambia@flsmidth.com',
                'phone' => '+260 211 012 345',
                'payment_terms' => 'Net 60',
            ],
            [
                'company_name' => 'Metso Outotec Zambia Ltd',
                'vendor_display_name' => 'Metso Outotec',
                'salutation' => 'Mr',
                'first_name' => 'Markku',
                'last_name' => 'Virtanen',
                'email' => 'zambia@metso.com',
                'phone' => '+260 211 123 456',
                'payment_terms' => 'Net 60',
            ],
            [
                'company_name' => 'Weir Minerals Zambia Ltd',
                'vendor_display_name' => 'Weir Minerals',
                'salutation' => 'Mr',
                'first_name' => 'Stuart',
                'last_name' => 'Campbell',
                'email' => 'zambia@weirminerals.com',
                'phone' => '+260 211 234 567',
                'payment_terms' => 'Net 45',
            ],

            // ==================== SAFETY EQUIPMENT SUPPLIERS ====================
            [
                'company_name' => 'Safety Africa Zambia Ltd',
                'vendor_display_name' => 'Safety Africa',
                'salutation' => 'Mr',
                'first_name' => 'Mark',
                'last_name' => 'Zulu',
                'email' => 'sales@safetyafrica.co.zm',
                'phone' => '+260 212 345 678',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'MSA Africa Zambia',
                'vendor_display_name' => 'MSA Africa',
                'salutation' => 'Mr',
                'first_name' => 'Brian',
                'last_name' => 'Mwanza',
                'email' => 'zambia@msasafety.com',
                'phone' => '+260 212 456 789',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Honeywell Safety Zambia',
                'vendor_display_name' => 'Honeywell Safety',
                'salutation' => 'Mrs',
                'first_name' => 'Susan',
                'last_name' => 'Chanda',
                'email' => 'safety@honeywell.co.zm',
                'phone' => '+260 212 567 890',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Drager Safety Zambia Ltd',
                'vendor_display_name' => 'Drager Safety',
                'salutation' => 'Mr',
                'first_name' => 'Klaus',
                'last_name' => 'Mueller',
                'email' => 'zambia@drager.com',
                'phone' => '+260 212 678 901',
                'payment_terms' => 'Net 45',
            ],

            // ==================== SPARE PARTS & CONSUMABLES ====================
            [
                'company_name' => 'BMG World Zambia Ltd',
                'vendor_display_name' => 'BMG World',
                'salutation' => 'Mr',
                'first_name' => 'Kevin',
                'last_name' => 'Muskwe',
                'email' => 'zambia@bmgworld.com',
                'phone' => '+260 212 789 012',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'SKF Zambia Ltd',
                'vendor_display_name' => 'SKF Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Magnus',
                'last_name' => 'Eriksson',
                'email' => 'zambia@skf.com',
                'phone' => '+260 212 890 123',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Timken Zambia Ltd',
                'vendor_display_name' => 'Timken Zambia',
                'salutation' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Miller',
                'email' => 'zambia@timken.com',
                'phone' => '+260 212 901 234',
                'payment_terms' => 'Net 30',
            ],

            // ==================== CONVEYOR BELTS & RUBBER ====================
            [
                'company_name' => 'Dunlop Industrial Zambia Ltd',
                'vendor_display_name' => 'Dunlop Industrial',
                'salutation' => 'Mr',
                'first_name' => 'Peter',
                'last_name' => 'Van Rooyen',
                'email' => 'zambia@dunlopindustrial.com',
                'phone' => '+260 212 012 345',
                'payment_terms' => 'Net 45',
            ],
            [
                'company_name' => 'Bridgestone Mining Zambia',
                'vendor_display_name' => 'Bridgestone Mining',
                'salutation' => 'Mr',
                'first_name' => 'Hiroshi',
                'last_name' => 'Tanaka',
                'email' => 'mining@bridgestone.co.zm',
                'phone' => '+260 212 123 456',
                'payment_terms' => 'Net 45',
            ],

            // ==================== WELDING & FABRICATION ====================
            [
                'company_name' => 'Afrox Zambia Ltd',
                'vendor_display_name' => 'Afrox Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'zambia@afrox.com',
                'phone' => '+260 212 234 567',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'Lincoln Electric Zambia',
                'vendor_display_name' => 'Lincoln Electric',
                'salutation' => 'Mr',
                'first_name' => 'George',
                'last_name' => 'Sakala',
                'email' => 'zambia@lincolnelectric.com',
                'phone' => '+260 212 345 678',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'ESAB Zambia Ltd',
                'vendor_display_name' => 'ESAB Zambia',
                'salutation' => 'Mr',
                'first_name' => 'James',
                'last_name' => 'Mwila',
                'email' => 'zambia@esab.com',
                'phone' => '+260 212 456 789',
                'payment_terms' => 'Net 30',
            ],

            // ==================== CHEMICALS & REAGENTS ====================
            [
                'company_name' => 'Sasol Mining Chemicals Zambia',
                'vendor_display_name' => 'Sasol Mining Chemicals',
                'salutation' => 'Mr',
                'first_name' => 'Thabo',
                'last_name' => 'Mokoena',
                'email' => 'mining@sasol.co.zm',
                'phone' => '+260 212 567 890',
                'payment_terms' => 'Net 30',
            ],
            [
                'company_name' => 'BASF Mining Solutions Zambia',
                'vendor_display_name' => 'BASF Mining',
                'salutation' => 'Mr',
                'first_name' => 'Hans',
                'last_name' => 'Schmidt',
                'email' => 'mining@basf.co.zm',
                'phone' => '+260 212 678 901',
                'payment_terms' => 'Net 45',
            ],

            // ==================== LOCAL SUPPLIERS ====================
            [
                'company_name' => 'Copperbelt Industrial Supplies Ltd',
                'vendor_display_name' => 'Copperbelt Industrial',
                'salutation' => 'Mr',
                'first_name' => 'Stanley',
                'last_name' => 'Mumba',
                'email' => 'sales@copperbeltindustrial.co.zm',
                'phone' => '+260 212 789 012',
                'payment_terms' => 'Net 15',
            ],
            [
                'company_name' => 'Kitwe Engineering Works Ltd',
                'vendor_display_name' => 'Kitwe Engineering',
                'salutation' => 'Mr',
                'first_name' => 'Joseph',
                'last_name' => 'Mwape',
                'email' => 'info@kitweengineering.co.zm',
                'phone' => '+260 212 890 123',
                'payment_terms' => 'Net 15',
            ],
            [
                'company_name' => 'Ndola Steel Fabricators Ltd',
                'vendor_display_name' => 'Ndola Steel Fabricators',
                'salutation' => 'Mr',
                'first_name' => 'Emmanuel',
                'last_name' => 'Phiri',
                'email' => 'sales@ndolasteel.co.zm',
                'phone' => '+260 212 901 234',
                'payment_terms' => 'Net 15',
            ],

            // ==================== LOGISTICS & FREIGHT ====================
            [
                'company_name' => 'Bollore Transport & Logistics Zambia',
                'vendor_display_name' => 'Bollore Logistics',
                'salutation' => 'Mr',
                'first_name' => 'Philippe',
                'last_name' => 'Martin',
                'email' => 'zambia@bollore.com',
                'phone' => '+260 211 234 567',
                'payment_terms' => 'Net 15',
            ],
            [
                'company_name' => 'Maersk Zambia Ltd',
                'vendor_display_name' => 'Maersk Zambia',
                'salutation' => 'Mr',
                'first_name' => 'Henrik',
                'last_name' => 'Nielsen',
                'email' => 'zambia@maersk.com',
                'phone' => '+260 211 345 678',
                'payment_terms' => 'Net 15',
            ],
            [
                'company_name' => 'Imperial Logistics Zambia',
                'vendor_display_name' => 'Imperial Logistics',
                'salutation' => 'Mr',
                'first_name' => 'Lambert',
                'last_name' => 'Pretorius',
                'email' => 'zambia@imperiallogistics.com',
                'phone' => '+260 211 456 789',
                'payment_terms' => 'Net 15',
            ],
        ];
    }
}
