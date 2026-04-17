<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\QuotationResource\Pages\CreateQuotation;
use App\Filament\Resources\QuotationResource\Pages\EditQuotation;
use App\Filament\Resources\QuotationResource\Pages\ListQuotations;
use App\Filament\Resources\QuotationResource\Pages\ViewQuotation;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\DeliveryMethod;
use App\Models\PaymentTerm;
use App\Models\SalesPerson;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationResourceTest extends TestCase
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
        $this->get(QuotationResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(QuotationResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $quotation = $this->createQuotation();

        $this->get(QuotationResource::getUrl('edit', ['record' => $quotation]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_view_page()
    {
        $quotation = $this->createQuotation();

        $this->get(QuotationResource::getUrl('view', ['record' => $quotation]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_quotations()
    {
        $quotations = collect();
        for ($i = 0; $i < 5; $i++) {
            $quotations->push($this->createQuotation());
        }

        Livewire::test(ListQuotations::class)
            ->assertCanSeeTableRecords($quotations);
    }

    /** @test */
    public function it_can_create_a_quotation()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);
        $deliveryMethod = DeliveryMethod::factory()->create(['team_id' => $this->team->id]);
        $salesPerson = SalesPerson::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'quotation_number' => 'QO-2024-0001',
            'reference_number' => 'REF-2024-0001',
            'quotation_date' => now()->format('Y-m-d'),
            'expected_shippment_date' => now()->addDays(30)->format('Y-m-d'),
            'payment_term_id' => $paymentTerm->id,
            'delivery_method_id' => $deliveryMethod->id,
            'sales_person_id' => $salesPerson->id,
            'items' => [],
            'sub_total' => 0,
            'total' => 0,
            'discount' => 0,
            'adjustment' => 0,
            'status' => 'pending',
        ];

        Livewire::test(CreateQuotation::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Quotation::class, [
            'quotation_number' => 'QO-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $quotation = $this->createQuotation();

        Livewire::test(EditQuotation::class, ['record' => $quotation->getRouteKey()])
            ->assertFormSet([
                'quotation_number' => $quotation->quotation_number,
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_view()
    {
        $quotation = $this->createQuotation();

        Livewire::test(ViewQuotation::class, ['record' => $quotation->getRouteKey()])
            ->assertFormSet([
                'quotation_number' => $quotation->quotation_number,
            ]);
    }

    /** @test */
    public function it_can_update_a_quotation()
    {
        $quotation = $this->createQuotation();
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'quotation_number' => 'QO-2024-UPDATED',
            'reference_number' => 'REF-2024-UPDATED',
            'quotation_date' => now()->format('Y-m-d'),
            'expected_shippment_date' => now()->addDays(14)->format('Y-m-d'),
            'items' => [],
            'sub_total' => 1000,
            'total' => 1000,
            'discount' => 0,
            'adjustment' => 0,
        ];

        Livewire::test(EditQuotation::class, ['record' => $quotation->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Quotation::class, [
            'id' => $quotation->id,
            'quotation_number' => 'QO-2024-UPDATED',
        ]);
    }

    /** @test */
    public function it_can_delete_a_quotation()
    {
        $quotation = $this->createQuotation();

        Livewire::test(EditQuotation::class, ['record' => $quotation->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($quotation);
    }

    protected function createQuotation(): Quotation
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);
        $deliveryMethod = DeliveryMethod::factory()->create(['team_id' => $this->team->id]);
        $salesPerson = SalesPerson::factory()->create(['team_id' => $this->team->id]);

        return Quotation::factory()->create([
            'team_id' => $this->team->id,
            'customer_id' => $customer->id,
            'payment_term_id' => $paymentTerm->id,
            'delivery_method_id' => $deliveryMethod->id,
            'sales_person_id' => $salesPerson->id,
        ]);
    }
}
