<?php

namespace Modules\StrategicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmKpi extends Model
{
    protected $table = 'sm_kpis';

    protected $fillable = [
        'goal_id',
        'code',
        'name',
        'unit',
        'target_value',
        'baseline_value',
        'formula',
        'year',
    ];

    protected $casts = [
        'target_value'   => 'decimal:2',
        'baseline_value' => 'decimal:2',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SmGoal::class, 'goal_id');
    }

    public function actuals(): HasMany
    {
        return $this->hasMany(SmKpiActual::class, 'kpi_id');
    }
}
