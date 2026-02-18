<?php

namespace Modules\StrategicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmKpiActual extends Model
{
    protected $table = 'sm_kpi_actuals';

    protected $fillable = [
        'kpi_id',
        'period',
        'actual_value',
        'notes',
    ];

    protected $casts = [
        'actual_value' => 'decimal:2',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(SmKpi::class, 'kpi_id');
    }
}
