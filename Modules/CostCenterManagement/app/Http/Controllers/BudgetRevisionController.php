<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Services\BudgetTrackingService;

class BudgetRevisionController extends Controller
{
    protected BudgetTrackingService $budgetService;

    public function __construct(BudgetTrackingService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Display list of budget revisions pending approval
     */
    public function index(): View
    {
        // Budget revisions are tracked by revision_number > 0
        $pendingRevisions = CostCenterBudget::where('revision_number', '>', 0)
            ->whereNull('approved_at')
            ->with(['costCenter', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('costcentermanagement::approval.budget-revisions', compact('pendingRevisions'));
    }

    /**
     * Show form to create budget revision
     */
    public function create(CostCenterBudget $budget): View
    {
        Gate::authorize('revise', $budget);

        return view('costcentermanagement::budget.revise', compact('budget'));
    }

    /**
     * Submit budget revision
     */
    public function store(Request $request, CostCenterBudget $budget): RedirectResponse
    {
        Gate::authorize('revise', $budget);

        $validated = $request->validate([
            'new_budget_amount' => 'required|numeric|min:0',
            'justification' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $revisedBudget = $this->budgetService->reviseBudget(
                $budget->id,
                ['budget_amount' => $validated['new_budget_amount']],
                $validated['justification']
            );

            DB::commit();

            return redirect()
                ->route('ccm.budget-revisions.index')
                ->with('success', 'Budget revision berhasil disubmit untuk approval');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "Gagal submit budget revision: {$e->getMessage()}");
        }
    }

    /**
     * Approve budget revision
     */
    public function approve(CostCenterBudget $budget): RedirectResponse
    {
        if (!auth()->user()->can('cost-center-management.approve')) {
            abort(403, 'Anda tidak memiliki akses untuk approve budget revision');
        }

        // Tidak bisa approve revision sendiri
        if ($budget->created_by === auth()->id()) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat approve budget revision yang Anda buat sendiri');
        }

        DB::beginTransaction();
        try {
            $budget->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Budget revision berhasil diapprove');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal approve budget revision: {$e->getMessage()}");
        }
    }

    /**
     * Reject budget revision
     */
    public function reject(Request $request, CostCenterBudget $budget): RedirectResponse
    {
        if (!auth()->user()->can('cost-center-management.approve')) {
            abort(403, 'Anda tidak memiliki akses untuk reject budget revision');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Mark as rejected by deleting the revision
            $budget->update([
                'revision_justification' => $validated['rejection_reason'] . ' [REJECTED]',
                'updated_by' => auth()->id(),
            ]);

            // Optionally soft delete or mark as rejected
            $budget->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Budget revision berhasil direject');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal reject budget revision: {$e->getMessage()}");
        }
    }
}
