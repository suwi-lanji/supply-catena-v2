<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class DatabaseChannel extends \Illuminate\Notifications\Channels\DatabaseChannel
{
    public function buildPayload($notifiable, Notification $notification)
    {
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
            'tenant_id' => $notification->tenant_id,
        ];
    }
}
