<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function salesOrder()
    {
        $teamId = Filament::getTenant()->id;
        return $this->belongsTo(SalesOrder::class)->when($teamId, function($query, $id) {
            return $query->where('team_id', $id);
        });
    }

    public function customer()
    {
        $teamId = Filament::getTenant()->id;
        return $this->belongsTo(Customer::class)->when($teamId, function($query, $id) {
            return $query->where('team_id', $id);
        });
    }
}
