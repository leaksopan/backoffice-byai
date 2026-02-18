<?php

namespace Modules\StrategicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmSuccessFactor extends Model
{
    protected $table = 'sm_success_factors';

    protected $fillable = [
        'goal_id',
        'name',
        'description',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SmGoal::class, 'goal_id');
    }
}
