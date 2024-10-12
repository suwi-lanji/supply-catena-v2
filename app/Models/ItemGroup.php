<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class ItemGroup extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'images' => 'array',
        ];
    }
    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);;
    }
    public function items(): BelongsToMany {
        return $this->belongsToMany(Item::class, 'item_group_item');
    }
    public function purchases_account(): hasOne {
        return $this->hasOne(PurchasesAccount::class)->where('team_id', Filament::getTenant()->id);
    }

    public function sales_account(): HasOne {
        return $this->hasOne(SalesAccount::class)->where('team_id', Filament::getTenant()->id);
    }

    public function manufucturer(): HasOne {
        return $this->hasOne(Manufucturer::class)->where('team_id', Filament::getTenant()->id);
    }

    public function brand(): HasOne {
        return $this->hasOne(Brand::class)->where('team_id', Filament::getTenant()->id);
    }

    public function warehouse(): HasOne {
        return $this->hasOne(Warehouse::class)->where('team_id', Filament::getTenant()->id);
    }
}
