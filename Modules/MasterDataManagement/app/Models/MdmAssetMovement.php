<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmAssetMovement extends Model
{
    protected $fillable = [
        'asset_id',
        'from_location_id',
        'to_location_id',
        'movement_date',
        'reason',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MdmAsset::class, 'asset_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(MdmOrganizationUnit::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(MdmOrganizationUnit::class, 'to_location_id');
    }
}
