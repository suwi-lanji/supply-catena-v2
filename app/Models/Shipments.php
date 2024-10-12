<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Shipments extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'packages' => 'array',
        ];
    }
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function customer() {
        return $this->hasOne(Customer::class)->where('team_id', Filament::getTenant()->id);
    }

    public function delivery_method() {
        return $this->belongsTo(DeliveryMethod::class)->where('team_id', Filament::getTenant()->id);
    }
}
