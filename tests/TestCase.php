<?php

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create a team and set as current tenant
        $this->team = Team::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Set the current panel for Filament
        Filament::setCurrentPanel(
            Filament::getPanel('dashboard'),
        );

        // Set the current tenant
        Filament::setTenant($this->team);
    }
}
