<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class SalesAccount extends Model
{
    use HasFactory;
    
    protected $table = "sales_accounts";
    protected $guarded = [];
    
}
