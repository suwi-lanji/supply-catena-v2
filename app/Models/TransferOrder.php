<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Filament\Facades\Filament;
class TransferOrder extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'costs' => 'array',
        ];
    }
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);;
    }

    public function source_warehouse(): HasOne {
        return $this->hasOne(Warehouse::class)->where('team_id', Filament::getTenant()->id);
    }
    
    public function destination_warehouse(): HasOne {
        return $this->hasOne(Warehouse::class)->where('team_id', Filament::getTenant()->id);
    }
}
