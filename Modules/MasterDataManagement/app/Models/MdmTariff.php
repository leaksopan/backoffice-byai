<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdmTariff extends MdmBaseModel
{
    protected $fillable = [
        'service_id',
        'service_class',
        'tariff_amount',
        'start_date',
        'end_date',
        'payer_type',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tariff_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(MdmServiceCatalog::class, 'service_id');
    }

    public function breakdowns(): HasMany
    {
        return $this->hasMany(MdmTariffBreakdown::class, 'tariff_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeForClass($query, string $class)
    {
        return $query->where('service_class', $class);
    }

    public function scopeForPayer($query, ?string $payerType)
    {
        return $query->where('payer_type', $payerType);
    }

    public function scopeValidOn($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }

    public function getEntityType(): string
    {
        return 'tariff';
    }
}
