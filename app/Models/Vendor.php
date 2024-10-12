<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Vendor extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);;
    }

    public function bills(): BelongsToMany {
        return $this->belongsToMany(Bill::class);
    }

    public function items(): BelongsToMany {
        return $this->belongsToMany(Item::class);
    }

}
