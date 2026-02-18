<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Modules\CostCenterManagement\Traits\Auditable;

class CostCenterBudget extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'cost_center_id',
        'fiscal_year',
        'period_month',
        'category',
        'budget_amount',
        'actual_amount',
        'variance_amount',
        'utilization_percentage',
        'revision_number',
        'revision_justification',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'period_month' => 'integer',
        'budget_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'utilization_percentage' => 'decimal:2',
        'revision_number' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Modules\CostCenterManagement\Database\Factories\CostCenterBudgetFactory::new();
    }

    /**
     * Relationships
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeForPeriod($query, int $fiscalYear, int $periodMonth)
    {
        return $query->where('fiscal_year', $fiscalYear)
                     ->where('period_month', $periodMonth);
    }

    public function scopeForCostCenter($query, int $costCenterId)
    {
        return $query->where('cost_center_id', $costCenterId);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeLatestRevision($query)
    {
        return $query->orderBy('revision_number', 'desc');
    }

    public function scopeCurrentRevision($query)
    {
        return $query->whereRaw('revision_number = (
            SELECT MAX(revision_number) 
            FROM cost_center_budgets AS ccb 
            WHERE ccb.cost_center_id = cost_center_budgets.cost_center_id 
            AND ccb.fiscal_year = cost_center_budgets.fiscal_year 
            AND ccb.period_month = cost_center_budgets.period_month 
            AND ccb.category = cost_center_budgets.category
        )');
    }

    public function scopeOverUtilized($query, float $threshold = 80.0)
    {
        return $query->where('utilization_percentage', '>', $threshold);
    }

    public function scopeWithVariance($query)
    {
        return $query->where('variance_amount', '!=', 0);
    }

    /**
     * Helper methods
     */
    public function isOverBudget(): bool
    {
        return $this->actual_amount > $this->budget_amount;
    }

    public function isOverThreshold(float $threshold = 80.0): bool
    {
        return $this->utilization_percentage > $threshold;
    }

    public function getRemainingBudget(): float
    {
        return max(0, $this->budget_amount - $this->actual_amount);
    }

    public function getVarianceType(): string
    {
        if ($this->variance_amount < 0) {
            return 'favorable'; // actual < budget
        } elseif ($this->variance_amount > 0) {
            return 'unfavorable'; // actual > budget
        }
        return 'on_target';
    }
}
