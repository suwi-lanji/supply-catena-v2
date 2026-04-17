<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrant()
    {
        return $this->belongsTo(User::class, 'regr_id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modr_id');
    }
}
