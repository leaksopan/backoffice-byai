<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceLine extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the members for the service line.
     */
    public function members(): HasMany
    {
        return $this->hasMany(ServiceLineMember::class);
    }

    /**
     * Get the cost centers for the service line.
     */
    public function costCenters()
    {
        return $this->hasManyThrough(
            CostCenter::class,
            ServiceLineMember::class,
            'service_line_id',
            'id',
            'id',
            'cost_center_id'
        );
    }

    /**
     * Scope a query to only include active service lines.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
