<?php

namespace App\Models;

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
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
