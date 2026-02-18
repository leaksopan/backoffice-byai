<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdmOrganizationUnit extends MdmBaseModel
{
    protected $table = 'mdm_organization_units';

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'hierarchy_path',
        'level',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Relationship: Parent unit
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MdmOrganizationUnit::class, 'parent_id');
    }

    /**
     * Relationship: Child units
     */
    public function children(): HasMany
    {
        return $this->hasMany(MdmOrganizationUnit::class, 'parent_id');
    }

    /**
     * Scope: Active units only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getEntityType(): string
    {
        return 'organization_unit';
    }
}
