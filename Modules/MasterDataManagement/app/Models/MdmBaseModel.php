<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\MasterDataManagement\Traits\DispatchesMasterDataEvents;

abstract class MdmBaseModel extends Model
{
    use DispatchesMasterDataEvents;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    abstract public function getEntityType(): string;
}
