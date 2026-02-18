<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLineMember extends Model
{
    protected $fillable = [
        'service_line_id',
        'cost_center_id',
        'allocation_percentage',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
    ];

    /**
     * Get the service line that owns the member.
     */
    public function serviceLine(): BelongsTo
    {
        return $this->belongsTo(ServiceLine::class);
    }

    /**
     * Get the cost center that owns the member.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
