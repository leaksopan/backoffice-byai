<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdmAsset extends MdmBaseModel
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'acquisition_value',
        'acquisition_date',
        'useful_life_years',
        'depreciation_method',
        'residual_value',
        'current_location_id',
        'condition',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'acquisition_value' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'acquisition_date' => 'date',
        'is_active' => 'boolean',
        'useful_life_years' => 'integer',
    ];

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(MdmOrganizationUnit::class, 'current_location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MdmAssetMovement::class, 'asset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, int $locationId)
    {
        return $query->where('current_location_id', $locationId);
    }

    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    public function isDepreciable(): bool
    {
        return !is_null($this->useful_life_years) 
            && !is_null($this->depreciation_method)
            && $this->useful_life_years > 0;
    }

    public function getEntityType(): string
    {
        return 'asset';
    }
}
