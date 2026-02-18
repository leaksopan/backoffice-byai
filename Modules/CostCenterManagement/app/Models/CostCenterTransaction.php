<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostCenterTransaction extends Model
{
    protected $fillable = [
        'cost_center_id',
        'transaction_date',
        'transaction_type',
        'category',
        'amount',
        'reference_type',
        'reference_id',
        'description',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            self::validateActiveCostCenter($transaction);
        });

        static::updating(function ($transaction) {
            self::validateActiveCostCenter($transaction);
        });
    }

    /**
     * Validate that cost center is active before creating/updating transaction.
     */
    protected static function validateActiveCostCenter($transaction)
    {
        $costCenter = CostCenter::find($transaction->cost_center_id);
        
        if (!$costCenter) {
            throw new \Exception("Cost center tidak ditemukan dengan ID {$transaction->cost_center_id}");
        }

        if (!$costCenter->is_active) {
            throw new \Exception("Cost center ID {$transaction->cost_center_id} tidak aktif dan tidak dapat menerima transaksi baru");
        }
    }

    /**
     * Get the cost center that owns the transaction.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * Scope a query to only include direct costs.
     */
    public function scopeDirectCosts($query)
    {
        return $query->where('transaction_type', 'direct_cost');
    }

    /**
     * Scope a query to only include allocated costs.
     */
    public function scopeAllocatedCosts($query)
    {
        return $query->where('transaction_type', 'allocated_cost');
    }

    /**
     * Scope a query to only include revenue.
     */
    public function scopeRevenue($query)
    {
        return $query->where('transaction_type', 'revenue');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
