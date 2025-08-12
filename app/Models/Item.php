<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    use HasFactory;

    protected $table = 'item';

    protected $guarded = [];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sales_account(): HasOne
    {
        return $this->hasOne(SalesAccount::class)->where('team_id', Filament::getTenant()->id);
    }

    public function purchases_account(): hasOne
    {
        return $this->hasOne(PurchasesAccount::class)->where('team_id', Filament::getTenant()->id);
    }

    public function registrant()
    {
        return $this->belongsTo(User::class, 'regr_id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modr_id');
    }

    public function preferred_vendor(): hasOne
    {
        return $this->hasOne(Vendor::class)->where('team_id', Filament::getTenant()->id);
    }

    public function manufucturer(): hasOne
    {
        return $this->hasOne(Manufucturer::class)->where('team_id', Filament::getTenant()->id);
    }

    public function brand(): hasOne
    {
        return $this->hasOne(Brand::class)->where('team_id', Filament::getTenant()->id);
    }

    public function item_groups(): BelongsToMany
    {
        return $this->belongsToMany(ItemGroup::class, 'item_group_item')->where('team_id', Filament::getTenant()->id);
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class)->where('team_id', Filament::getTenant()->id);
    }
}
