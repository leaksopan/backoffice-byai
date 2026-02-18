<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostPoolMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_pool_id',
        'cost_center_id',
        'is_contributor',
    ];

    protected $casts = [
        'is_contributor' => 'boolean',
    ];

    // Relationships
    public function costPool(): BelongsTo
    {
        return $this->belongsTo(CostPool::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    // Scopes
    public function scopeContributors($query)
    {
        return $query->where('is_contributor', true);
    }

    public function scopeTargets($query)
    {
        return $query->where('is_contributor', false);
    }
}
