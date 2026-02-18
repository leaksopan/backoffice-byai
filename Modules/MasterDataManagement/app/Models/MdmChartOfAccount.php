<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdmChartOfAccount extends MdmBaseModel
{
    protected $table = 'mdm_chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'category',
        'normal_balance',
        'parent_id',
        'level',
        'is_header',
        'is_active',
        'external_code',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Relationship: Parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MdmChartOfAccount::class, 'parent_id');
    }

    /**
     * Relationship: Child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(MdmChartOfAccount::class, 'parent_id');
    }

    /**
     * Scope: Active accounts only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Postable accounts (non-header)
     */
    public function scopePostable($query)
    {
        return $query->where('is_header', false);
    }

    /**
     * Scope: Header accounts only
     */
    public function scopeHeaders($query)
    {
        return $query->where('is_header', true);
    }

    /**
     * Check if account is postable (can be used in transactions)
     */
    public function isPostable(): bool
    {
        return !$this->is_header;
    }

    /**
     * Check if account has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function getEntityType(): string
    {
        return 'chart_of_account';
    }
}
