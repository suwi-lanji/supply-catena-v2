<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\PaymentTermResource;
use App\Filament\Resources\PaymentTermResource\Pages\CreatePaymentTerm;
use App\Filament\Resources\PaymentTermResource\Pages\EditPaymentTerm;
use App\Filament\Resources\PaymentTermResource\Pages\ListPaymentTerms;
use App\Models\PaymentTerm;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTermResourceTest extends TestCase
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
        $this->get(PaymentTermResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(PaymentTermResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);

        $this->get(PaymentTermResource::getUrl('edit', ['record' => $paymentTerm]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_payment_terms()
    {
        $paymentTerms = PaymentTerm::factory()->count(5)->create(['team_id' => $this->team->id]);

        Livewire::test(ListPaymentTerms::class)
            ->assertCanSeeTableRecords($paymentTerms);
    }

    /** @test */
    public function it_can_create_a_payment_term()
    {
        $newData = [
            'name' => 'Net 30',
            'account_type' => 'Business',
            'bank' => 'ZANACO',
            'account_name' => 'Test Account',
            'account_number' => '1234567890',
            'branch' => 'Kitwe Branch',
            'swift_code' => 'ZANAZMLX',
            'branch_number' => '001',
        ];

        Livewire::test(CreatePaymentTerm::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(PaymentTerm::class, [
            'name' => 'Net 30',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_update_a_payment_term()
    {
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'name' => 'Net 60',
            'account_type' => 'Business',
            'bank' => 'Standard Chartered',
            'account_name' => 'Updated Account',
            'account_number' => '0987654321',
            'branch' => 'Lusaka Branch',
            'swift_code' => 'SCBLZMLX',
            'branch_number' => '002',
        ];

        Livewire::test(EditPaymentTerm::class, ['record' => $paymentTerm->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(PaymentTerm::class, [
            'id' => $paymentTerm->id,
            'name' => 'Net 60',
        ]);
    }

    /** @test */
    public function it_can_delete_a_payment_term()
    {
        $paymentTerm = PaymentTerm::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditPaymentTerm::class, ['record' => $paymentTerm->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($paymentTerm);
    }
}
