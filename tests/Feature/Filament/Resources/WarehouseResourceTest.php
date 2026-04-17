<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\WarehouseResource;
use App\Filament\Resources\WarehouseResource\Pages\CreateWarehouse;
use App\Filament\Resources\WarehouseResource\Pages\EditWarehouse;
use App\Filament\Resources\WarehouseResource\Pages\ListWarehouses;
use App\Filament\Resources\WarehouseResource\Pages\ViewWarehouse;
use App\Models\Warehouse;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseResourceTest extends TestCase
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
            'has_warehouses' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        Filament::setTenant($this->team);
    }

    /** @test */
    public function it_can_render_the_list_page()
    {
        $this->get(WarehouseResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(WarehouseResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $warehouse = Warehouse::factory()->create(['team_id' => $this->team->id]);

        $this->get(WarehouseResource::getUrl('edit', ['record' => $warehouse]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_view_page()
    {
        $warehouse = Warehouse::factory()->create(['team_id' => $this->team->id]);

        $this->get(WarehouseResource::getUrl('view', ['record' => $warehouse]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_warehouses()
    {
        $warehouses = Warehouse::factory()->count(5)->create(['team_id' => $this->team->id]);

        Livewire::test(ListWarehouses::class)
            ->assertCanSeeTableRecords($warehouses);
    }

    /** @test */
    public function it_can_create_a_warehouse()
    {
        $newData = [
            'name' => 'Main Warehouse',
            'location' => 'Kitwe Industrial Area',
            'is_default' => true,
        ];

        Livewire::test(CreateWarehouse::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Warehouse::class, [
            'name' => 'Main Warehouse',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_update_a_warehouse()
    {
        $warehouse = Warehouse::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'name' => 'Updated Warehouse',
            'location' => 'Lusaka Business Park',
            'is_default' => false,
        ];

        Livewire::test(EditWarehouse::class, ['record' => $warehouse->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Warehouse::class, [
            'id' => $warehouse->id,
            'name' => 'Updated Warehouse',
        ]);
    }

    /** @test */
    public function it_can_delete_a_warehouse()
    {
        $warehouse = Warehouse::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditWarehouse::class, ['record' => $warehouse->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($warehouse);
    }
}
