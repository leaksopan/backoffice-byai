<?php

namespace Modules\MasterDataManagement\Models;

use Database\Factories\MdmServiceCatalogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmServiceCatalog extends MdmBaseModel
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit_id',
        'inacbg_code',
        'standard_duration',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'standard_duration' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MdmOrganizationUnit::class, 'unit_id');
    }

    public function tariffs()
    {
        return $this->hasMany(\Modules\MasterDataManagement\Models\MdmTariff::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    protected static function newFactory()
    {
        return MdmServiceCatalogFactory::new();
    }

    public function getEntityType(): string
    {
        return 'service_catalog';
    }
}