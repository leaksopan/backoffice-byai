<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class CostPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'pool_type',
        'allocation_base',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function members(): HasMany
    {
        return $this->hasMany(CostPoolMember::class);
    }

    public function contributors(): HasMany
    {
        return $this->hasMany(CostPoolMember::class)->where('is_contributor', true);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(CostPoolMember::class)->where('is_contributor', false);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('pool_type', $type);
    }
}
