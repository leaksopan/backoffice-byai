<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Modules\CostCenterManagement\Traits\Auditable;

class CostCenter extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'code',
        'name',
        'type',
        'classification',
        'organization_unit_id',
        'parent_id',
        'hierarchy_path',
        'level',
        'manager_user_id',
        'is_active',
        'effective_date',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'level' => 'integer',
    ];

    protected static function newFactory()
    {
        return \Modules\CostCenterManagement\Database\Factories\CostCenterFactory::new();
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(\Modules\CostCenterManagement\Models\CostCenter::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(\Modules\CostCenterManagement\Models\CostCenter::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterDataManagement\Models\MdmOrganizationUnit::class, 'organization_unit_id');
    }

    public function allocationRulesAsSource(): HasMany
    {
        return $this->hasMany(\Modules\CostCenterManagement\Models\AllocationRule::class, 'source_cost_center_id');
    }

    public function allocationRuleTargets(): HasMany
    {
        return $this->hasMany(\Modules\CostCenterManagement\Models\AllocationRuleTarget::class, 'target_cost_center_id');
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
        return $query->where('type', $type);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}

