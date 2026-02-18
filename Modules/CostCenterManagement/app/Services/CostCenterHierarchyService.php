<?php

namespace Modules\CostCenterManagement\Services;

use Modules\CostCenterManagement\Models\CostCenter;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CostCenterHierarchyService
{
    /**
     * Validasi bahwa parent tidak membuat circular reference
     * 
     * @param int $costCenterId ID cost center yang akan diupdate
     * @param int|null $parentId ID parent yang akan diset
     * @return bool true jika valid (tidak ada circular), false jika ada circular
     */
    public function validateNoCircularReference(int $costCenterId, ?int $parentId): bool
    {
        // Jika parent null, tidak ada circular reference
        if ($parentId === null) {
            return true;
        }

        // Jika parent sama dengan cost center sendiri, circular
        if ($parentId === $costCenterId) {
            return false;
        }

        // Cek apakah parent adalah descendant dari cost center
        $descendants = $this->getDescendants($costCenterId);
        
        return !$descendants->contains('id', $parentId);
    }

    /**
     * Update hierarchy path untuk cost center dan semua descendants
     * 
     * @param CostCenter $costCenter
     * @return void
     */
    public function updateHierarchyPath(CostCenter $costCenter): void
    {
        // Build hierarchy path dari parent
        $path = $this->buildHierarchyPath($costCenter);
        $level = substr_count($path, '/');

        // Update cost center
        $costCenter->update([
            'hierarchy_path' => $path,
            'level' => $level,
        ]);

        // Update semua children secara rekursif
        $children = $costCenter->children;
        foreach ($children as $child) {
            $this->updateHierarchyPath($child);
        }
    }

    /**
     * Build hierarchy path dari parent chain
     * 
     * @param CostCenter $costCenter
     * @return string
     */
    protected function buildHierarchyPath(CostCenter $costCenter): string
    {
        $path = (string) $costCenter->id;

        if ($costCenter->parent_id) {
            $parent = $costCenter->parent;
            if ($parent) {
                $path = $parent->hierarchy_path . '/' . $path;
            }
        }

        return $path;
    }

    /**
     * Dapatkan semua descendant cost centers (children, grandchildren, dst)
     * 
     * @param int $costCenterId
     * @return Collection
     */
    public function getDescendants(int $costCenterId): Collection
    {
        $costCenter = CostCenter::find($costCenterId);
        
        if (!$costCenter) {
            return collect();
        }

        $descendants = collect();
        $this->collectDescendants($costCenter, $descendants);

        return $descendants;
    }

    /**
     * Rekursif collect descendants
     * 
     * @param CostCenter $costCenter
     * @param Collection $descendants
     * @return void
     */
    protected function collectDescendants(CostCenter $costCenter, Collection &$descendants): void
    {
        $children = $costCenter->children;

        foreach ($children as $child) {
            $descendants->push($child);
            $this->collectDescendants($child, $descendants);
        }
    }

    /**
     * Dapatkan semua ancestor cost centers (parent, grandparent, dst)
     * 
     * @param int $costCenterId
     * @return Collection
     */
    public function getAncestors(int $costCenterId): Collection
    {
        $costCenter = CostCenter::find($costCenterId);
        
        if (!$costCenter) {
            return collect();
        }

        $ancestors = collect();
        $current = $costCenter;

        while ($current->parent_id) {
            $parent = $current->parent;
            if ($parent) {
                $ancestors->push($parent);
                $current = $parent;
            } else {
                break;
            }
        }

        return $ancestors;
    }

    /**
     * Cek apakah cost center bisa dihapus (tidak punya children)
     * 
     * @param CostCenter $costCenter
     * @return bool
     */
    public function canDelete(CostCenter $costCenter): bool
    {
        return $costCenter->children()->count() === 0;
    }

    /**
     * Konsolidasi biaya dari cost center dan semua descendants
     * 
     * @param int $costCenterId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function consolidateCosts(int $costCenterId, Carbon $startDate, Carbon $endDate): float
    {
        $costCenter = CostCenter::find($costCenterId);
        
        if (!$costCenter) {
            return 0.0;
        }

        // Get direct costs untuk cost center ini
        $directCosts = $costCenter->transactions()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Get costs dari semua descendants
        $descendants = $this->getDescendants($costCenterId);
        $descendantCosts = 0.0;

        foreach ($descendants as $descendant) {
            $descendantCosts += $descendant->transactions()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');
        }

        return $directCosts + $descendantCosts;
    }
}
