<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ModuleMenu extends Model
{
    protected $fillable = [
        'module_key',
        'section',
        'label',
        'route_name',
        'url',
        'icon',
        'sort_order',
        'permission_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }
}
