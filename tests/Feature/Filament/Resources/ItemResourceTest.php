<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\Pages\ListItems;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemResourceTest extends TestCase
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
        $this->get(ItemResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_create_page()
    {
        $this->get(ItemResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_the_edit_page()
    {
        $item = Item::factory()->create(['team_id' => $this->team->id]);

        $this->get(ItemResource::getUrl('edit', ['record' => $item]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_list_items()
    {
        $items = Item::factory()->count(10)->create(['team_id' => $this->team->id]);

        Livewire::test(ListItems::class)
            ->assertCanSeeTableRecords($items);
    }

    /** @test */
    public function it_can_create_an_item()
    {
        $newData = [
            'name' => 'Test Item',
            'item_type' => 'Goods',
            'sku' => 'SKU-TEST-001',
            'selling_price' => 150.00,
            'cost_price' => 100.00,
            'track_inventory_for_this_item' => true,
            'stock_on_hand' => 50,
            'reorder_level' => 10,
            'returnable_item' => true,
            'description' => 'Test item description',
        ];

        Livewire::test(CreateItem::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Item::class, [
            'name' => 'Test Item',
            'item_type' => 'Goods',
            'sku' => 'SKU-TEST-001',
            'team_id' => $this->team->id,
        ]);
    }

    /** @test */
    public function it_can_validate_required_fields_on_create()
    {
        Livewire::test(CreateItem::class)
            ->fillForm([
                'name' => null,
                'sku' => null,
                'selling_price' => null,
                'cost_price' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'sku' => 'required',
                'selling_price' => 'required',
                'cost_price' => 'required',
            ]);
    }

    /** @test */
    public function it_can_retrieve_data_for_edit()
    {
        $item = Item::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditItem::class, ['record' => $item->getRouteKey()])
            ->assertFormSet([
                'name' => $item->name,
                'sku' => $item->sku,
                'item_type' => $item->item_type,
            ]);
    }

    /** @test */
    public function it_can_update_an_item()
    {
        $item = Item::factory()->create(['team_id' => $this->team->id]);

        $newData = [
            'name' => 'Updated Item',
            'item_type' => 'Service',
            'sku' => 'SKU-UPDATED-001',
            'selling_price' => 200.00,
            'cost_price' => 150.00,
            'track_inventory_for_this_item' => false,
            'returnable_item' => false,
            'description' => 'Updated item description',
        ];

        Livewire::test(EditItem::class, ['record' => $item->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Item::class, [
            'id' => $item->id,
            'name' => 'Updated Item',
            'item_type' => 'Service',
        ]);
    }

    /** @test */
    public function it_can_delete_an_item()
    {
        $item = Item::factory()->create(['team_id' => $this->team->id]);

        Livewire::test(EditItem::class, ['record' => $item->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($item);
    }

    /** @test */
    public function it_can_force_delete_an_item()
    {
        $item = Item::factory()->create(['team_id' => $this->team->id]);
        $item->delete();

        Livewire::test(ListItems::class)
            ->callTableAction('forceDelete', $item->getKey());

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    /** @test */
    public function it_can_restore_a_deleted_item()
    {
        $item = Item::factory()->create(['team_id' => $this->team->id]);
        $item->delete();

        Livewire::test(ListItems::class)
            ->callTableAction('restore', $item->getKey());

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    }
}
