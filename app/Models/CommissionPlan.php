<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommissionPlan extends Model
{
    protected $fillable = [
        'name',
        'percentage',
        'notes',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'commission_plan_category');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isEffective(Carbon $at = null): bool
    {
        $moment = $at ?: Carbon::now();

        if ($this->starts_at && $moment->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $moment->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
