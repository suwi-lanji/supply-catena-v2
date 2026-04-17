<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\PaymentTerm;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
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
        $this->get(CustomerResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(CustomerResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        $this->get(CustomerResource::getUrl('edit', ['record' => $customer]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_customers()
    {
        $customers = Customer::factory()->count(10)->create(['team_id' => $this->team->id]);

        Livewire::test(ListCustomers::class)
            ->assertCanSeeTableRecords($customers);
    }

    /** @test */
    public function it_can_create_a_customer()
    {
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_type' => 'Business',
            'salutation' => 'Mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_name' => 'Test Company',
            'company_display_name' => 'Test Company Display',
            'email' => 'john@testcompany.com',
            'phone' => '+260 123 456 789',
            'tpin' => '1234567890',
            'branch_id' => 'BR001',
            'useYn' => true,
            'regrNm' => $this->user->name,
            'regr_id' => $this->user->id,
            'modrNm' => $this->user->name,
            'modr_id' => $this->user->id,
            'payment_terms' => $paymentTerm->id,
            'billing_street_1' => '123 Main Street',
            'billing_city' => 'Kitwe',
            'billing_province' => 'Copperbelt',
            'billing_country' => 'Zambia',
            'billing_phone' => '+260 123 456 789',
            'shipping_street_1' => '123 Main Street',
            'shipping_city' => 'Kitwe',
            'shipping_province' => 'Copperbelt',
            'shipping_country' => 'Zambia',
            'shipping_phone' => '+260 123 456 789',
        ];

        Livewire::test(CreateCustomer::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Customer::class, [
            'company_display_name' => 'Test Company Display',
            'email' => 'john@testcompany.com',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_validate_required_fields_on_create()
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'first_name' => null,
                'last_name' => null,
                'company_name' => null,
                'email' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'last_name' => 'required',
                'company_name' => 'required',
                'email' => 'required',
            ]);
    }

    /** @test */
    public function it_can_validate_email_format()
    {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'customer_type' => 'Business',
                'salutation' => 'Mr',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'company_name' => 'Test Company',
                'company_display_name' => 'Test Company Display',
                'email' => 'invalid-email',
                'phone' => '+260 123 456 789',
                'tpin' => '1234567890',
                'branch_id' => 'BR001',
                'useYn' => true,
                'regrNm' => $this->user->name,
                'regr_id' => $this->user->id,
                'modrNm' => $this->user->name,
                'modr_id' => $this->user->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->assertFormSet([
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
            ]);
    }

    /** @test */
    public function it_can_update_a_customer()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_type' => 'Business',
            'salutation' => 'Mrs',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'company_name' => 'Updated Company',
            'company_display_name' => 'Updated Company Display',
            'email' => 'jane@updatedcompany.com',
            'phone' => '+260 987 654 321',
            'tpin' => '0987654321',
            'branch_id' => 'BR002',
            'useYn' => true,
            'regrNm' => $this->user->name,
            'regr_id' => $this->user->id,
            'modrNm' => $this->user->name,
            'modr_id' => $this->user->id,
            'payment_terms' => $paymentTerm->id,
            'billing_street_1' => '456 Updated Street',
            'billing_city' => 'Lusaka',
            'billing_province' => 'Lusaka',
            'billing_country' => 'Zambia',
            'billing_phone' => '+260 987 654 321',
            'shipping_street_1' => '456 Updated Street',
            'shipping_city' => 'Lusaka',
            'shipping_province' => 'Lusaka',
            'shipping_country' => 'Zambia',
            'shipping_phone' => '+260 987 654 321',
        ];

        Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Customer::class, [
            'id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'company_display_name' => 'Updated Company Display',
        ]);
    }

    /** @test */
    public function it_can_validate_required_fields_on_update()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->fillForm([
                'first_name' => null,
                'company_name' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'company_name' => 'required',
            ]);
    }

    /** @test */
    public function it_can_delete_a_customer()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($customer);
    }
}
