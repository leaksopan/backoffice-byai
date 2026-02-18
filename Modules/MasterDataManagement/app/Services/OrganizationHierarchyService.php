<?php

namespace Modules\MasterDataManagement\Services;

use Illuminate\Support\Collection;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

class OrganizationHierarchyService
{
    /**
     * Validate bahwa tidak terjadi circular reference dalam hierarki
     */
    public function validateNoCircularReference(int $unitId, ?int $parentId): bool
    {
        if ($parentId === null) {
            return true;
        }

        // Unit tidak bisa menjadi parent dari dirinya sendiri
        if ($unitId === $parentId) {
            return false;
        }

        // Cek apakah parent adalah descendant dari unit ini
        $parent = MdmOrganizationUnit::find($parentId);
        if (!$parent) {
            return true;
        }

        // Traverse ke atas untuk cek circular reference
        $currentParent = $parent;
        while ($currentParent) {
            if ($currentParent->id === $unitId) {
                return false;
            }
            $currentParent = $currentParent->parent;
        }

        return true;
    }

    /**
     * Update hierarchy path untuk unit dan semua descendants
     */
    public function updateHierarchyPath(MdmOrganizationUnit $unit): void
    {
        // Reload unit dengan parent relationship
        $unit->load('parent');
        
        // Build hierarchy path
        $path = $this->buildHierarchyPath($unit);
        $unit->hierarchy_path = $path;
        $unit->level = substr_count($path, '/');
        $unit->save();

        // Update semua children
        foreach ($unit->children as $child) {
            $this->updateHierarchyPath($child);
        }
    }

    /**
     * Build hierarchy path dari root ke unit ini
     */
    private function buildHierarchyPath(MdmOrganizationUnit $unit): string
    {
        $path = [(string)$unit->id];
        $current = $unit->parent;

        while ($current) {
            array_unshift($path, (string)$current->id);
            $current = $current->parent;
        }

        return implode('/', $path);
    }

    /**
     * Get semua descendants dari unit
     */
    public function getDescendants(int $unitId): Collection
    {
        $unit = MdmOrganizationUnit::find($unitId);
        if (!$unit) {
            return collect();
        }

        return $this->collectDescendants($unit);
    }

    /**
     * Recursively collect descendants
     */
    private function collectDescendants(MdmOrganizationUnit $unit): Collection
    {
        $descendants = collect();

        foreach ($unit->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->collectDescendants($child));
        }

        return $descendants;
    }

    /**
     * Check apakah unit bisa dihapus (tidak punya children)
     */
    public function canDelete(MdmOrganizationUnit $unit): bool
    {
        return $unit->children()->count() === 0;
    }
}
