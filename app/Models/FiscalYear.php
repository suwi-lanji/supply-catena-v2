<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'closed_by',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the team that owns this fiscal year.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who closed this fiscal year.
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get the budgets for this fiscal year.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(AccountBudget::class, 'fiscal_year_id');
    }

    /**
     * Check if a date falls within this fiscal year.
     *
     * @param mixed $date
     * @return bool
     */
    public function containsDate($date): bool
    {
        $date = $date instanceof \DateTimeInterface ? $date : \Carbon\Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Check if the fiscal year is open.
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return !$this->is_closed;
    }

    /**
     * Close the fiscal year.
     *
     * @param int $userId
     * @return bool
     */
    public function close(int $userId): bool
    {
        if ($this->is_closed) {
            return false;
        }

        $this->is_closed = true;
        $this->closed_by = $userId;
        $this->closed_at = now();

        return $this->save();
    }

    /**
     * Scope to get open fiscal years.
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope to get closed fiscal years.
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * Find the fiscal year for a given date.
     *
     * @param mixed $date
     * @return static|null
     */
    public static function findByDate($date, int $teamId): ?static
    {
        $date = $date instanceof \DateTimeInterface ? $date : \Carbon\Carbon::parse($date);

        return static::where('team_id', $teamId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }
}
