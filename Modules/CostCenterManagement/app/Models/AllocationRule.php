<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Modules\CostCenterManagement\Traits\Auditable;

class AllocationRule extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'code',
        'name',
        'source_cost_center_id',
        'allocation_base',
        'allocation_formula',
        'is_active',
        'effective_date',
        'end_date',
        'approval_status',
        'approved_by',
        'approved_at',
        'justification',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Modules\CostCenterManagement\Database\Factories\AllocationRuleFactory::new();
    }

    // Relationships
    public function sourceCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'source_cost_center_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(AllocationRuleTarget::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now();
        return $query->where('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }

    public function scopeActiveAndApproved($query)
    {
        return $query->active()->approved();
    }
}
