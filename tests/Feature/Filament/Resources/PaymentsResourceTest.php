<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\PaymentsMadeResource;
use App\Filament\Resources\PaymentsMadeResource\Pages\CreatePaymentsMade;
use App\Filament\Resources\PaymentsMadeResource\Pages\EditPaymentsMade;
use App\Filament\Resources\PaymentsMadeResource\Pages\ListPaymentsMades;
use App\Filament\Resources\PaymentsMadeResource\Pages\ViewPaymentsMade;
use App\Filament\Resources\PaymentsReceivedResource;
use App\Filament\Resources\PaymentsReceivedResource\Pages\CreatePaymentsReceived;
use App\Filament\Resources\PaymentsReceivedResource\Pages\ListPaymentsReceiveds;
use App\Models\PaymentsMade;
use App\Models\PaymentsReceived;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentsResourceTest extends TestCase
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

    // ==================== Payments Made Tests ====================

    /** @test */
    public function it_can_render_payments_made_list_page()
    {
        $this->get(PaymentsMadeResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_payments_made_create_page()
    {
        $this->get(PaymentsMadeResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_payments_made()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);
        $payments = PaymentsMade::factory()->count(5)->create([
            'team_id' => $this->team->id,
            'vendor_id' => $vendor->id,
        ]);

        Livewire::test(ListPaymentsMades::class)
            ->assertCanSeeTableRecords($payments);
    }

    /** @test */
    public function it_can_create_a_payment_made()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'vendor_id' => $vendor->id,
            'payment_number' => 'PM-2024-0001',
            'payment_date' => now()->format('Y-m-d'),
            'payment_made' => 5000,
            'payment_mode' => 'Bank Transfer',
            'paid_through' => 'ZANACO',
            'reference_number' => 'REF-2024-0001',
            'notes' => 'Payment for supplies',
            'items' => [],
        ];

        Livewire::test(CreatePaymentsMade::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(PaymentsMade::class, [
            'payment_number' => 'PM-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_view_a_payment_made()
    {
        $vendor = Vendor::factory()->create(['team_id' => $this->team->id]);
        $payment = PaymentsMade::factory()->create([
            'team_id' => $this->team->id,
            'vendor_id' => $vendor->id,
        ]);

        $this->get(PaymentsMadeResource::getUrl('view', ['record' => $payment]))
            ->assertSuccessful();
    }

    // ==================== Payments Received Tests ====================

    /** @test */
    public function it_can_render_payments_received_list_page()
    {
        $this->get(PaymentsReceivedResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_payments_received_create_page()
    {
        $this->get(PaymentsReceivedResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_payments_received()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $payments = PaymentsReceived::factory()->count(5)->create([
            'team_id' => $this->team->id,
            'customer_id' => $customer->id,
        ]);

        Livewire::test(ListPaymentsReceiveds::class)
            ->assertCanSeeTableRecords($payments);
    }

    /** @test */
    public function it_can_create_a_payment_received()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-2024-0001',
            'payment_date' => now()->format('Y-m-d'),
            'amount_received' => 10000,
            'bank_charges' => 0,
            'payment_mode' => 'Bank Transfer',
            'paid_through' => 'ZANACO',
            'reference_number' => 'REF-2024-0001',
            'notes' => 'Payment for invoice',
            'items' => [],
        ];

        Livewire::test(CreatePaymentsReceived::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(PaymentsReceived::class, [
            'payment_number' => 'PAY-2024-0001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_view_a_payment_received()
    {
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);
        $payment = PaymentsReceived::factory()->create([
            'team_id' => $this->team->id,
            'customer_id' => $customer->id,
        ]);

        $this->get(PaymentsReceivedResource::getUrl('view', ['record' => $payment]))
            ->assertSuccessful();
    }
}
