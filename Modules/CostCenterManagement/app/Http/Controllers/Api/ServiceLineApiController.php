<?php

namespace Modules\CostCenterManagement\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\CostCenterTransaction;

class ServiceLineApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceLine::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $serviceLines = $query->with('members.costCenter')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Service lines retrieved successfully',
            'data' => $serviceLines,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function show(ServiceLine $serviceLine): JsonResponse
    {
        $serviceLine->load('members.costCenter');

        return response()->json([
            'success' => true,
            'message' => 'Service line retrieved successfully',
            'data' => $serviceLine,
            'meta' => [
                'request_id' => request()->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function costAnalysis(ServiceLine $serviceLine, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);

        $costCenterIds = $serviceLine->members->pluck('cost_center_id');

        $transactions = CostCenterTransaction::whereIn('cost_center_id', $costCenterIds)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
            ->get();

        $totalCost = 0;
        $costByCenter = [];

        foreach ($serviceLine->members as $member) {
            $centerTransactions = $transactions->where('cost_center_id', $member->cost_center_id);
            $centerCost = $centerTransactions->sum('amount');
            
            // Apply allocation percentage for shared cost centers
            $allocatedCost = $centerCost * ($member->allocation_percentage / 100);
            $totalCost += $allocatedCost;

            $costByCenter[] = [
                'cost_center_id' => $member->cost_center_id,
                'cost_center_name' => $member->costCenter->name,
                'total_cost' => $centerCost,
                'allocation_percentage' => $member->allocation_percentage,
                'allocated_cost' => $allocatedCost,
                'by_category' => $centerTransactions->groupBy('category')->map(function ($items) use ($member) {
                    return $items->sum('amount') * ($member->allocation_percentage / 100);
                }),
            ];
        }

        $analysis = [
            'service_line_id' => $serviceLine->id,
            'service_line_name' => $serviceLine->name,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'total_cost' => $totalCost,
            'cost_by_center' => $costByCenter,
            'cost_by_category' => $transactions->groupBy('category')->map(function ($items) {
                return $items->sum('amount');
            }),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Cost analysis retrieved successfully',
            'data' => $analysis,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function profitability(ServiceLine $serviceLine, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);

        $costCenterIds = $serviceLine->members->pluck('cost_center_id');

        // Calculate total cost
        $costTransactions = CostCenterTransaction::whereIn('cost_center_id', $costCenterIds)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
            ->get();

        $totalCost = 0;
        foreach ($serviceLine->members as $member) {
            $centerCost = $costTransactions->where('cost_center_id', $member->cost_center_id)->sum('amount');
            $totalCost += $centerCost * ($member->allocation_percentage / 100);
        }

        // Calculate total revenue
        $revenueTransactions = CostCenterTransaction::whereIn('cost_center_id', $costCenterIds)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('transaction_type', 'revenue')
            ->get();

        $totalRevenue = 0;
        foreach ($serviceLine->members as $member) {
            $centerRevenue = $revenueTransactions->where('cost_center_id', $member->cost_center_id)->sum('amount');
            $totalRevenue += $centerRevenue * ($member->allocation_percentage / 100);
        }

        $profit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        $profitability = [
            'service_line_id' => $serviceLine->id,
            'service_line_name' => $serviceLine->name,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'profit' => $profit,
            'profit_margin_percentage' => round($profitMargin, 2),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Profitability analysis retrieved successfully',
            'data' => $profitability,
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_line_ids' => 'required|array|min:2',
            'service_line_ids.*' => 'exists:service_lines,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'meta' => [
                    'request_id' => $request->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422);
        }

        $periodStart = Carbon::parse($request->period_start);
        $periodEnd = Carbon::parse($request->period_end);

        $serviceLines = ServiceLine::whereIn('id', $request->service_line_ids)
            ->with('members.costCenter')
            ->get();

        $comparison = [];

        foreach ($serviceLines as $serviceLine) {
            $costCenterIds = $serviceLine->members->pluck('cost_center_id');

            $costTransactions = CostCenterTransaction::whereIn('cost_center_id', $costCenterIds)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
                ->get();

            $totalCost = 0;
            foreach ($serviceLine->members as $member) {
                $centerCost = $costTransactions->where('cost_center_id', $member->cost_center_id)->sum('amount');
                $totalCost += $centerCost * ($member->allocation_percentage / 100);
            }

            $revenueTransactions = CostCenterTransaction::whereIn('cost_center_id', $costCenterIds)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('transaction_type', 'revenue')
                ->get();

            $totalRevenue = 0;
            foreach ($serviceLine->members as $member) {
                $centerRevenue = $revenueTransactions->where('cost_center_id', $member->cost_center_id)->sum('amount');
                $totalRevenue += $centerRevenue * ($member->allocation_percentage / 100);
            }

            $profit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

            $comparison[] = [
                'service_line_id' => $serviceLine->id,
                'service_line_name' => $serviceLine->name,
                'total_cost' => $totalCost,
                'total_revenue' => $totalRevenue,
                'profit' => $profit,
                'profit_margin_percentage' => round($profitMargin, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Service line comparison retrieved successfully',
            'data' => [
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'comparison' => $comparison,
            ],
            'meta' => [
                'request_id' => $request->id(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
