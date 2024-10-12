<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Customer extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);;
    }

    public function payment_term(): hasOne {
        return $this->hasOne(PaymentTerm::class)->where('team_id', Filament::getTenant()->id);
    }
    public function registrant() {
        return $this->belongsTo(User::class, 'regr_id');
    }
    public function modifier() {
        return $this->belongsTo(User::class, 'modr_id');
    }
}
