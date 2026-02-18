<?php

namespace Modules\StrategicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmVision extends Model
{
    protected $table = 'sm_visions';

    protected $fillable = [
        'title',
        'vision_text',
        'mission_text',
        'period_start',
        'period_end',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function goals(): HasMany
    {
        return $this->hasMany(SmGoal::class, 'vision_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(SmEvaluation::class, 'vision_id');
    }
}
