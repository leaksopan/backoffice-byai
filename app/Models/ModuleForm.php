<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleForm extends Model
{
    protected $fillable = [
        'module_id',
        'key',
        'name',
        'schema_json',
        'is_active',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
