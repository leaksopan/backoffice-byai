<?php

namespace Modules\StrategicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmRoadmap extends Model
{
    protected $table = 'sm_roadmaps';

    protected $fillable = [
        'goal_id',
        'title',
        'description',
        'year',
        'priority',
        'status',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SmGoal::class, 'goal_id');
    }
}
