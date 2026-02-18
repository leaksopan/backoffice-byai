<?php

namespace Modules\StrategicManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmEvaluation extends Model
{
    protected $table = 'sm_evaluations';

    protected $fillable = [
        'vision_id',
        'year',
        'title',
        'summary',
        'overall_score',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'evaluated_at'  => 'datetime',
    ];

    public function vision(): BelongsTo
    {
        return $this->belongsTo(SmVision::class, 'vision_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
