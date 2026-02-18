<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\Models\MdmTariff;
use Modules\MasterDataManagement\Models\MdmHumanResource;
use Modules\MasterDataManagement\Models\MdmAsset;

class MdmDashboardController extends Controller
{
    public function index()
    {
        $statistics = $this->getSummaryStatistics();
        $recentChanges = $this->getRecentChanges();
        $dataQuality = $this->getDataQualityMetrics();

        return view('masterdatamanagement::dashboard', compact('statistics', 'recentChanges', 'dataQuality'));
    }

    /**
     * Get summary statistics untuk setiap kategori data master
     */
    private function getSummaryStatistics(): array
    {
        return [
            'organization_units' => [
                'total' => MdmOrganizationUnit::count(),
                'active' => MdmOrganizationUnit::active()->count(),
                'inactive' => MdmOrganizationUnit::where('is_active', false)->count(),
                'by_type' => MdmOrganizationUnit::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ],
            'chart_of_accounts' => [
                'total' => MdmChartOfAccount::count(),
                'active' => MdmChartOfAccount::active()->count(),
                'inactive' => MdmChartOfAccount::where('is_active', false)->count(),
                'postable' => MdmChartOfAccount::postable()->count(),
                'headers' => MdmChartOfAccount::headers()->count(),
                'by_category' => MdmChartOfAccount::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray(),
            ],
            'funding_sources' => [
                'total' => MdmFundingSource::count(),
                'active' => MdmFundingSource::active()->count(),
                'inactive' => MdmFundingSource::where('is_active', false)->count(),
                'active_today' => MdmFundingSource::activeOn(now())->count(),
                'by_type' => MdmFundingSource::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ],
            'services' => [
                'total' => MdmServiceCatalog::count(),
                'active' => MdmServiceCatalog::active()->count(),
                'inactive' => MdmServiceCatalog::where('is_active', false)->count(),
                'by_category' => MdmServiceCatalog::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray(),
            ],
            'tariffs' => [
                'total' => MdmTariff::count(),
                'active' => MdmTariff::active()->count(),
                'inactive' => MdmTariff::where('is_active', false)->count(),
                'valid_today' => MdmTariff::active()->validOn(now())->count(),
                'by_class' => MdmTariff::select('service_class', DB::raw('count(*) as count'))
                    ->groupBy('service_class')
                    ->pluck('count', 'service_class')
                    ->toArray(),
            ],
            'human_resources' => [
                'total' => MdmHumanResource::count(),
                'active' => MdmHumanResource::active()->count(),
                'inactive' => MdmHumanResource::where('is_active', false)->count(),
                'by_category' => MdmHumanResource::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray(),
                'by_employment_status' => MdmHumanResource::select('employment_status', DB::raw('count(*) as count'))
                    ->groupBy('employment_status')
                    ->pluck('count', 'employment_status')
                    ->toArray(),
            ],
            'assets' => [
                'total' => MdmAsset::count(),
                'active' => MdmAsset::active()->count(),
                'inactive' => MdmAsset::where('is_active', false)->count(),
                'depreciable' => MdmAsset::whereNotNull('useful_life_years')
                    ->whereNotNull('depreciation_method')
                    ->count(),
                'by_category' => MdmAsset::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray(),
                'by_condition' => MdmAsset::select('condition', DB::raw('count(*) as count'))
                    ->groupBy('condition')
                    ->pluck('count', 'condition')
                    ->toArray(),
                'total_value' => MdmAsset::sum('acquisition_value'),
            ],
        ];
    }

    /**
     * Get recent changes log (last 20 changes)
     */
    private function getRecentChanges(): array
    {
        $changes = [];

        // Organization Units
        $orgUnits = MdmOrganizationUnit::latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'entity_type' => 'Organization Unit',
                'entity_name' => $item->name,
                'entity_code' => $item->code,
                'action' => 'Updated',
                'timestamp' => $item->updated_at,
                'user_id' => $item->updated_by,
            ]);

        // Chart of Accounts
        $coas = MdmChartOfAccount::latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'entity_type' => 'Chart of Account',
                'entity_name' => $item->name,
                'entity_code' => $item->code,
                'action' => 'Updated',
                'timestamp' => $item->updated_at,
                'user_id' => $item->updated_by,
            ]);

        // Funding Sources
        $fundingSources = MdmFundingSource::latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'entity_type' => 'Funding Source',
                'entity_name' => $item->name,
                'entity_code' => $item->code,
                'action' => 'Updated',
                'timestamp' => $item->updated_at,
                'user_id' => $item->updated_by,
            ]);

        // Services
        $services = MdmServiceCatalog::latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'entity_type' => 'Service',
                'entity_name' => $item->name,
                'entity_code' => $item->code,
                'action' => 'Updated',
                'timestamp' => $item->updated_at,
                'user_id' => $item->updated_by,
            ]);

        // Merge and sort by timestamp
        $changes = collect()
            ->merge($orgUnits)
            ->merge($coas)
            ->merge($fundingSources)
            ->merge($services)
            ->sortByDesc('timestamp')
            ->take(20)
            ->values()
            ->toArray();

        return $changes;
    }

    /**
     * Get data quality metrics
     */
    private function getDataQualityMetrics(): array
    {
        return [
            'organization_units' => [
                'completeness' => $this->calculateCompleteness(MdmOrganizationUnit::class, ['code', 'name', 'type']),
                'missing_description' => MdmOrganizationUnit::whereNull('description')->count(),
                'orphaned_units' => MdmOrganizationUnit::whereNotNull('parent_id')
                    ->whereDoesntHave('parent')
                    ->count(),
            ],
            'chart_of_accounts' => [
                'completeness' => $this->calculateCompleteness(MdmChartOfAccount::class, ['code', 'name', 'category', 'normal_balance']),
                'missing_description' => MdmChartOfAccount::whereNull('description')->count(),
                'missing_external_code' => MdmChartOfAccount::whereNull('external_code')->count(),
                'orphaned_accounts' => MdmChartOfAccount::whereNotNull('parent_id')
                    ->whereDoesntHave('parent')
                    ->count(),
            ],
            'funding_sources' => [
                'completeness' => $this->calculateCompleteness(MdmFundingSource::class, ['code', 'name', 'type', 'start_date']),
                'missing_end_date' => MdmFundingSource::whereNull('end_date')->count(),
                'expired' => MdmFundingSource::where('is_active', true)
                    ->whereNotNull('end_date')
                    ->where('end_date', '<', now())
                    ->count(),
            ],
            'services' => [
                'completeness' => $this->calculateCompleteness(MdmServiceCatalog::class, ['code', 'name', 'category', 'unit_id']),
                'missing_inacbg_code' => MdmServiceCatalog::whereNull('inacbg_code')->count(),
                'missing_duration' => MdmServiceCatalog::whereNull('standard_duration')->count(),
                'without_tariff' => MdmServiceCatalog::doesntHave('tariffs')->count(),
            ],
            'tariffs' => [
                'completeness' => $this->calculateCompleteness(MdmTariff::class, ['service_id', 'service_class', 'tariff_amount', 'start_date']),
                'expired' => MdmTariff::where('is_active', true)
                    ->whereNotNull('end_date')
                    ->where('end_date', '<', now())
                    ->count(),
                'without_breakdown' => MdmTariff::doesntHave('breakdowns')->count(),
            ],
            'human_resources' => [
                'completeness' => $this->calculateCompleteness(MdmHumanResource::class, ['nip', 'name', 'category', 'position']),
                'missing_salary' => MdmHumanResource::whereNull('basic_salary')->count(),
                'missing_hours' => MdmHumanResource::whereNull('effective_hours_per_week')->count(),
                'without_assignment' => MdmHumanResource::active()
                    ->doesntHave('activeAssignments')
                    ->count(),
                'over_allocated' => MdmHumanResource::active()
                    ->get()
                    ->filter(fn($hr) => $hr->total_allocation_percentage > 100)
                    ->count(),
            ],
            'assets' => [
                'completeness' => $this->calculateCompleteness(MdmAsset::class, ['code', 'name', 'category', 'acquisition_value', 'acquisition_date']),
                'missing_location' => MdmAsset::whereNull('current_location_id')->count(),
                'missing_depreciation_info' => MdmAsset::whereNull('useful_life_years')
                    ->orWhereNull('depreciation_method')
                    ->count(),
                'without_movement' => MdmAsset::doesntHave('movements')->count(),
            ],
        ];
    }

    /**
     * Calculate completeness percentage for a model
     */
    private function calculateCompleteness(string $modelClass, array $requiredFields): float
    {
        $total = $modelClass::count();
        
        if ($total === 0) {
            return 100.0;
        }

        $complete = $modelClass::query();
        
        foreach ($requiredFields as $field) {
            $complete->whereNotNull($field);
        }

        $completeCount = $complete->count();

        return round(($completeCount / $total) * 100, 2);
    }
}
