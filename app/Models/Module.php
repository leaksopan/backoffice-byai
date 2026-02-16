<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'entry_route',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function menus(): HasMany
    {
        return $this->hasMany(ModuleMenu::class, 'module_key', 'key');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(ModuleForm::class);
    }
}
