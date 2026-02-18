<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmTariffBreakdown extends Model
{
    protected $fillable = [
        'tariff_id',
        'component_type',
        'amount',
        'percentage',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(MdmTariff::class, 'tariff_id');
    }
}
