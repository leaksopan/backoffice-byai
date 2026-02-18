<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MdmHumanResource extends MdmBaseModel
{
    protected $fillable = [
        'nip',
        'name',
        'category',
        'position',
        'employment_status',
        'grade',
        'basic_salary',
        'effective_hours_per_week',
        'is_active',
        'hire_date',
        'termination_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'effective_hours_per_week' => 'integer',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(MdmHrAssignment::class, 'hr_id');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(MdmHrAssignment::class, 'hr_id')
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getTotalAllocationPercentageAttribute(): float
    {
        return $this->activeAssignments()->sum('allocation_percentage');
    }

    public function getEntityType(): string
    {
        return 'human_resource';
    }
}
