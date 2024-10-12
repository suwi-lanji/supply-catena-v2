<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsPurchased extends Model
{
    use HasFactory;
    protected $table = "items_purchased";
    protected $guarded = [];
    public function team() {
        return $this->belongsTo(Team::class);;
    }
    public function item() {
        return $this->belongsTo(Item::class);
    }
}
