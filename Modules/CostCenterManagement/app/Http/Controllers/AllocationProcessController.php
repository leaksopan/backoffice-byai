<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\CostCenterManagement\Services\CostAllocationService;
use Modules\CostCenterManagement\Models\AllocationJournal;
use Carbon\Carbon;

class AllocationProcessController extends Controller
{
    protected CostAllocationService $allocationService;

    public function __construct(CostAllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
        $this->middleware('auth');
        $this->middleware('can:cost-center-management.view')->only(['index', 'review', 'status']);
        $this->middleware('can:cost-center-management.allocate')->only(['create', 'execute']);
        $this->middleware('can:cost-center-management.approve')->only(['post', 'rollback']);
    }

    /**
     * Display list of allocation batches
     */
    public function index(): View
    {
        $batches = AllocationJournal::select('batch_id', 'period_start', 'period_end', 'status')
            ->selectRaw('MIN(created_at) as created_at')
            ->selectRaw('COUNT(*) as journal_count')
            ->selectRaw('SUM(source_amount) as total_source')
            ->selectRaw('SUM(allocated_amount) as total_allocated')
            ->groupBy('batch_id', 'period_start', 'period_end', 'status')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('costcentermanagement::allocation-process.index', compact('batches'));
    }

    /**
     * Show form to setup new allocation run
     */
    public function create(): View
    {
        return view('costcentermanagement::allocation-process.create');
    }

    /**
     * Execute allocation process
     */
    public function execute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        try {
            $periodStart = Carbon::parse($validated['period_start']);
            $periodEnd = Carbon::parse($validated['period_end']);

            // Store process status in cache for real-time monitoring
            $cacheKey = 'allocation_process_' . now()->timestamp;
            Cache::put($cacheKey, [
                'status' => 'processing',
                'progress' => 0,
                'message' => 'Memulai proses alokasi...',
            ], 3600);

            // Execute allocation
            $batchId = $this->allocationService->executeAllocation($periodStart, $periodEnd);

            // Update cache
            Cache::put($cacheKey, [
                'status' => 'completed',
                'progress' => 100,
                'message' => 'Proses alokasi selesai',
                'batch_id' => $batchId,
            ], 3600);

            return redirect()
                ->route('ccm.allocation-process.review', ['batchId' => $batchId])
                ->with('success', "Alokasi berhasil dijalankan. Batch ID: {$batchId}");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "Gagal menjalankan alokasi: {$e->getMessage()}");
        }
    }

    /**
     * Get real-time status of allocation process
     */
    public function status(Request $request): JsonResponse
    {
        $cacheKey = $request->input('cache_key');
        
        if (!$cacheKey) {
            return response()->json([
                'success' => false,
                'message' => 'Cache key is required',
            ], 400);
        }

        $status = Cache::get($cacheKey);

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Process not found or expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Review allocation results before posting
     */
    public function review(string $batchId): View
    {
        $journals = AllocationJournal::byBatch($batchId)
            ->with([
                'allocationRule',
                'sourceCostCenter',
                'targetCostCenter',
            ])
            ->get();

        if ($journals->isEmpty()) {
            abort(404, 'Batch tidak ditemukan');
        }

        // Calculate summary
        $summary = [
            'batch_id' => $batchId,
            'status' => $journals->first()->status,
            'period_start' => $journals->first()->period_start,
            'period_end' => $journals->first()->period_end,
            'journal_count' => $journals->count(),
            'total_source' => $journals->sum('source_amount'),
            'total_allocated' => $journals->sum('allocated_amount'),
            'difference' => $journals->sum('source_amount') - $journals->sum('allocated_amount'),
        ];

        // Group by source cost center
        $groupedBySources = $journals->groupBy('source_cost_center_id')->map(function ($group) {
            return [
                'source_cost_center' => $group->first()->sourceCostCenter,
                'total_source' => $group->sum('source_amount'),
                'total_allocated' => $group->sum('allocated_amount'),
                'journals' => $group,
            ];
        });

        return view('costcentermanagement::allocation-process.review', compact('summary', 'groupedBySources', 'journals'));
    }

    /**
     * Post allocation to GL
     */
    public function post(string $batchId): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $journals = AllocationJournal::byBatch($batchId)->draft()->get();

            if ($journals->isEmpty()) {
                throw new \Exception('Tidak ada journal draft untuk batch ini');
            }

            // Validate zero-sum before posting
            if (!$this->allocationService->validateZeroSum($journals)) {
                throw new \Exception('Validasi zero-sum gagal. Tidak dapat posting.');
            }

            // Update status to posted
            AllocationJournal::byBatch($batchId)->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);

            // TODO: In production, create GL entries here
            // This would integrate with General Ledger module

            DB::commit();

            return redirect()
                ->route('ccm.allocation-process.index')
                ->with('success', "Batch {$batchId} berhasil diposting ke GL");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal posting batch: {$e->getMessage()}");
        }
    }

    /**
     * Rollback allocation
     */
    public function rollback(string $batchId): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $journals = AllocationJournal::byBatch($batchId)->get();

            if ($journals->isEmpty()) {
                throw new \Exception('Batch tidak ditemukan');
            }

            $status = $journals->first()->status;

            if ($status === 'reversed') {
                throw new \Exception('Batch sudah di-rollback sebelumnya');
            }

            if ($status === 'posted') {
                // If already posted, create reversal entries
                AllocationJournal::byBatch($batchId)->update([
                    'status' => 'reversed',
                ]);

                // TODO: In production, create reversal GL entries here
                
            } else {
                // If still draft, just delete
                AllocationJournal::byBatch($batchId)->delete();
            }

            DB::commit();

            return redirect()
                ->route('ccm.allocation-process.index')
                ->with('success', "Batch {$batchId} berhasil di-rollback");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', "Gagal rollback batch: {$e->getMessage()}");
        }
    }
}
