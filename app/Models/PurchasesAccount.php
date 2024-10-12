<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class PurchasesAccount extends Model
{
    use HasFactory;
    
    protected $tables = "purchases_accounts";
    protected $guarded = [];
    public function items(): BelongsToMany {
        return $this->belongsToMany(Item::class);
    }
}
