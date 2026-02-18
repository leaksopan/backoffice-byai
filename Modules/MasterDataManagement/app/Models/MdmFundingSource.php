<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class MdmFundingSource extends MdmBaseModel
{
    protected $table = 'mdm_funding_sources';

    protected $fillable = [
        'code',
        'name',
        'type',
        'start_date',
        'end_date',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeActiveOn($query, Carbon $date)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $date);
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function isActiveOn(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Compare dates only (ignore time)
        $checkDate = $date->startOfDay();
        $start = $this->start_date->copy()->startOfDay();
        $end = $this->end_date?->copy()->startOfDay();

        if ($start->greaterThan($checkDate)) {
            return false;
        }

        if ($end && $end->lessThan($checkDate)) {
            return false;
        }

        return true;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'apbn' => 'APBN',
            'apbd_provinsi' => 'APBD Provinsi',
            'apbd_kab_kota' => 'APBD Kabupaten/Kota',
            'pnbp' => 'PNBP',
            'hibah' => 'Hibah',
            'pinjaman' => 'Pinjaman',
            'lainnya' => 'Lain-lain',
            default => $this->type,
        };
    }

    public function getEntityType(): string
    {
        return 'funding_source';
    }
}
