<?php

namespace App\Notifications;

use Filament\Facades\Filament;
use Filament\Notifications\Notification as BaseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class TenantNotification extends BaseNotification implements ShouldQueue
{
    protected function storeInDatabase($notifiable)
    {
        return DB::table('notifications')->create([
            'id' => $this->id,
            'type' => static::class,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => $this->toArray($notifiable),
            'read_at' => null,
            'tenant_id' => Filament::getTenant()->id,
        ]);
    }
}
