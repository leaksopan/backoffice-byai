<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\CostCenterManagement\Models\AllocationRule;

class AllocationRuleApprovalController extends Controller
{
    /**
     * Display list of allocation rules pending approval
     */
    public function index(): View
    {
        $pendingRules = AllocationRule::where('approval_status', 'pending')
            ->with(['sourceCostCenter', 'targets.targetCostCenter', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('costcentermanagement::approval.allocation-rules', compact('pendingRules'));
    }

    /**
     * Submit allocation rule for approval
     */
    public function submit(AllocationRule $allocationRule): RedirectResponse
    {
        Gate::authorize('submitForApproval', $allocationRule);

        DB::beginTransaction();
        try {
            // Validate rule sebelum submit
            if ($allocationRule->allocation_base === 'percentage') {
                $totalPercentage = $allocationRule->targets()->sum('allocation_percentage');
                if (abs($totalPercentage - 100.00) > 0.01) {
                    throw new \Exception("Total persentase alokasi harus 100%, saat ini: {$totalPercentage}%");
                }
            }

            // Validate source != target
            $sourceId = $allocationRule->source_cost_center_id;
            $hasSourceAsTarget = $allocationRule->targets()
                ->where('target_cost_center_id', $sourceId)
                ->exists();
            
            if ($hasSourceAsTarget) {
                throw new \Exception('Source dan target cost center tidak boleh sama');
            }

            $allocationRule->update([
                'approval_status' => 'pending',
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Allocation rule berhasil disubmit untuk approval');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal submit allocation rule: {$e->getMessage()}");
        }
    }

    /**
     * Approve allocation rule
     */
    public function approve(Request $request, AllocationRule $allocationRule): RedirectResponse
    {
        Gate::authorize('approve', $allocationRule);

        DB::beginTransaction();
        try {
            $allocationRule->update([
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Allocation rule berhasil diapprove');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal approve allocation rule: {$e->getMessage()}");
        }
    }

    /**
     * Reject allocation rule
     */
    public function reject(Request $request, AllocationRule $allocationRule): RedirectResponse
    {
        Gate::authorize('approve', $allocationRule);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $allocationRule->update([
                'approval_status' => 'rejected',
                'justification' => $validated['rejection_reason'],
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Allocation rule berhasil direject');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal reject allocation rule: {$e->getMessage()}");
        }
    }
}
