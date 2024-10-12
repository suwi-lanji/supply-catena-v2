<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class CreditNotes extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected function casts(): array {
        return [
            'items' => 'array',
        ];
    }
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function sales_person(): HasOne {
        return $this->hasOne(SalesPerson::class)->where('team_id', Filament::getTenant()->id);
    }

    public function customer(): HasOne {
        return $this->hasOne(Customer::class)->where('team_id', Filament::getTenant()->id);
    }
}
