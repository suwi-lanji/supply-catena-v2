<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Expense extends Model
{
    use HasFactory;
    
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);;
    }
}
