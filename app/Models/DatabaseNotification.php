<?php

namespace App\Models;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification {

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at'
    ];

    public function scopeForTenant($query, $tenantId) {
        return $query->where('tenant_id', $tenantId);
    }
}
