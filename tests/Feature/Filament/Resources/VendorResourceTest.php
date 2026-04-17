<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\VendorsResource;
use App\Filament\Resources\VendorsResource\Pages\CreateVendors;
use App\Filament\Resources\VendorsResource\Pages\EditVendors;
use App\Filament\Resources\VendorsResource\Pages\ListVendors;
use App\Models\Vendor;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->team = Team::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        Filament::setTenant($this->team);
    }

    /** @test */
    public function it_can_render_the_list_page()
    {
        $this->get(VendorsResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(VendorsResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        $this->get(VendorsResource::getUrl('edit', ['record' => $vendor]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_vendors()
    {
        $vendors = Vendor::factory()->count(10)->create(['team_id' => $this->team->id]);

        Livewire::test(ListVendors::class)
            ->assertCanSeeTableRecords($vendors);
    }

    /** @test */
    public function it_can_create_a_vendor()
    {
        $newData = [
            'salutation' => 'Mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_name' => 'Test Vendor Company',
            'vendor_display_name' => 'Test Vendor Display',
            'email' => 'vendor@testcompany.com',
            'phone' => '+260 123 456 789',
            'country' => 'Zambia',
            'city' => 'Kitwe',
            'address' => '123 Industrial Area',
            'postal_address' => 'P.O. Box 1234',
        ];

        Livewire::test(CreateVendors::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Vendor::class, [
            'vendor_display_name' => 'Test Vendor Display',
            'email' => 'vendor@testcompany.com',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_validate_required_fields_on_create()
    {
        Livewire::test(CreateVendors::class)
            ->fillForm([
                'vendor_display_name' => null,
                'email' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'vendor_display_name' => 'required',
                'email' => 'required',
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditVendors::class, ['record' => $vendor->getRouteKey()])
            ->assertFormSet([
                'first_name' => $vendor->first_name,
                'last_name' => $vendor->last_name,
                'email' => $vendor->email,
            ]);
    }

    /** @test */
    public function it_can_update_a_vendor()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'salutation' => 'Mrs',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'company_name' => 'Updated Vendor Company',
            'vendor_display_name' => 'Updated Vendor Display',
            'email' => 'updated@vendorcompany.com',
            'phone' => '+260 987 654 321',
            'country' => 'Zambia',
            'city' => 'Lusaka',
            'address' => '456 Business Park',
            'postal_address' => 'P.O. Box 5678',
        ];

        Livewire::test(EditVendors::class, ['record' => $vendor->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Vendor::class, [
            'id' => $vendor->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'vendor_display_name' => 'Updated Vendor Display',
        ]);
    }

    /** @test */
    public function it_can_delete_a_vendor()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditVendors::class, ['record' => $vendor->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($vendor);
    }
}
