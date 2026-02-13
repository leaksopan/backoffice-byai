<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'entry_route',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function menus(): HasMany
    {
        return $this->hasMany(ModuleMenu::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(ModuleForm::class);
    }
}
