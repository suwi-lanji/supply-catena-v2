<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    // Disable auto-incrementing since it's a UUID
    public $incrementing = false;

    // Specify that the primary key is of type string (UUID)
    protected $keyType = 'string';

    // Cast the 'data' field to array
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Automatically generate a UUID when creating a new Notification.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
