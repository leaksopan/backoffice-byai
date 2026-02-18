<?php

namespace Modules\CostCenterManagement\Livewire;

use Livewire\Component;
use Modules\CostCenterManagement\Models\CostCenter;
use Illuminate\Support\Collection;

class CostCenterTree extends Component
{
    public array $expandedNodes = [];
    public ?int $hoveredNodeId = null;
    public array $filters = [
        'type' => null,
        'is_active' => null,
        'search' => '',
    ];

    protected $listeners = ['refreshTree' => '$refresh'];

    public function mount()
    {
        // Expand root nodes by default
        $rootNodes = CostCenter::roots()->pluck('id')->toArray();
        $this->expandedNodes = $rootNodes;
    }

    public function toggleNode(int $nodeId)
    {
        if (in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes = array_diff($this->expandedNodes, [$nodeId]);
        } else {
            $this->expandedNodes[] = $nodeId;
        }
    }

    public function expandAll()
    {
        $allNodes = CostCenter::pluck('id')->toArray();
        $this->expandedNodes = $allNodes;
    }

    public function collapseAll()
    {
        $this->expandedNodes = [];
    }

    public function applyFilters()
    {
        $this->dispatch('refreshTree');
    }

    public function resetFilters()
    {
        $this->filters = [
            'type' => null,
            'is_active' => null,
            'search' => '',
        ];
        $this->dispatch('refreshTree');
    }

    public function getTreeData(): Collection
    {
        $query = CostCenter::with(['parent', 'children', 'manager', 'organizationUnit'])
            ->roots();

        // Apply filters
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if ($this->filters['is_active'] !== null) {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('code')->get();
    }

    public function loadChildren(int $parentId): Collection
    {
        $query = CostCenter::with(['parent', 'children', 'manager', 'organizationUnit'])
            ->where('parent_id', $parentId);

        // Apply filters to children as well
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if ($this->filters['is_active'] !== null) {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('code')->get();
    }

    public function render()
    {
        $rootNodes = $this->getTreeData();

        return view('costcentermanagement::livewire.cost-center-tree', [
            'rootNodes' => $rootNodes,
        ]);
    }
}
