<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DemoCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the demo company (Team)
        $company = Team::create([
            'name' => 'Copperbelt Mining Supplies Ltd',
            'portal_name' => 'CMS Portal',
            'industry' => 'Mining & Industrial Supplies',
            'business_location' => 'Kitwe, Copperbelt Province, Zambia',
            'street_1' => '2896 Bwana Mkubwa Road',
            'street_2' => 'Industrial Area',
            'city' => 'Kitwe',
            'province' => 'Copperbelt',
            'phone' => '+260 212 456 789',
            'fax' => '+260 212 456 790',
            'website' => 'www.copperbeltminingsupplies.com',
            'email' => 'info@copperbeltminingsupplies.com',
            'inventory_start' => now()->startOfYear(),
            'fiscal_year' => 'January - December',
            'language' => 'en',
            'logo' => 'logos/cms-logo.png',
            'currency_code' => 'ZMW',
            'currency_symbol' => 'K',
            'has_warehouses' => true,
        ]);

        // Create roles
        $roles = [
            'Admin' => 'Full system access',
            'Accountant' => 'Financial management and reporting',
            'Sales Manager' => 'Sales and customer management',
            'Purchasing Manager' => 'Procurement and vendor management',
            'Warehouse Manager' => 'Inventory and warehouse operations',
            'Sales Representative' => 'Create quotations and orders',
        ];

        foreach ($roles as $name => $description) {
            Role::create([
                'name' => $name,
                'description' => $description,
                'team_id' => $company->id,
                'guard_name' => 'web',
            ]);
        }

        // Create users with different roles
        $users = [
            [
                'name' => 'John Mwamba',
                'email' => 'admin@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Admin',
                'is_admin' => true,
            ],
            [
                'name' => 'Grace Lungu',
                'email' => 'accountant@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Accountant',
            ],
            [
                'name' => 'Peter Chisanga',
                'email' => 'sales@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Sales Manager',
            ],
            [
                'name' => 'Mary Tembo',
                'email' => 'purchasing@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Purchasing Manager',
            ],
            [
                'name' => 'James Banda',
                'email' => 'warehouse@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Warehouse Manager',
            ],
            [
                'name' => 'Ruth Phiri',
                'email' => 'salesrep1@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Sales Representative',
            ],
            [
                'name' => 'Steven Mulenga',
                'email' => 'salesrep2@copperbeltmining.com',
                'password' => 'password',
                'role' => 'Sales Representative',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'email_verified_at' => now(),
            ]);

            // Assign to team
            $company->users()->attach($user);

            // Make admin if specified
            if ($userData['is_admin'] ?? false) {
                $company->admins()->attach($user);
            }

            // Assign role with team context
            $role = Role::where('name', $userData['role'])
                ->where('team_id', $company->id)
                ->first();
            if ($role) {
                // Set the team context for permissions
                app()['cache']->forget('spatie.permission.cache');
                setPermissionsTeamId($company->id);
                $user->assignRole($role);
            }
        }

        $this->command->info('Demo company created: Copperbelt Mining Supplies Ltd');
        $this->command->info('Users created with roles: Admin, Accountant, Sales Manager, Purchasing Manager, Warehouse Manager, Sales Representatives');

        // Store company ID for other seeders
        $this->command->info("Company ID: {$company->id}");
    }
}
