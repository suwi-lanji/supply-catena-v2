<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VendorCredit extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected function casts(): array {
        return [
            'items' => 'array',
        ];
    }
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);;
    }
    public function vendor(): HasOne {
        return $this->hasOne(Vendor::class)->where('team_id', Filament::getTenant()->id);
    }
}
