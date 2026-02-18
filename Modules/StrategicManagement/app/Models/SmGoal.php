<?php

namespace Modules\StrategicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmGoal extends Model
{
    protected $table = 'sm_goals';

    protected $fillable = [
        'vision_id',
        'code',
        'name',
        'description',
        'sort',
    ];

    public function vision(): BelongsTo
    {
        return $this->belongsTo(SmVision::class, 'vision_id');
    }

    public function successFactors(): HasMany
    {
        return $this->hasMany(SmSuccessFactor::class, 'goal_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(SmKpi::class, 'goal_id');
    }

    public function roadmaps(): HasMany
    {
        return $this->hasMany(SmRoadmap::class, 'goal_id');
    }
}
