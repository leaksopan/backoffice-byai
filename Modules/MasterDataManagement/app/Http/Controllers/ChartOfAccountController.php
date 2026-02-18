<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\Services\CoaValidationService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChartOfAccountController extends Controller
{
    protected CoaValidationService $validationService;

    public function __construct(CoaValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Display listing with filters
     */
    public function index(Request $request): View
    {
        $query = MdmChartOfAccount::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by code or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->with('parent')
            ->orderBy('code')
            ->paginate(50);

        return view('masterdatamanagement::coa.index', compact('accounts'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $categories = ['asset', 'liability', 'equity', 'revenue', 'expense'];
        $normalBalances = ['debit', 'credit'];
        $parentAccounts = MdmChartOfAccount::active()->orderBy('code')->get();

        return view('masterdatamanagement::coa.create', compact('categories', 'normalBalances', 'parentAccounts'));
    }

    /**
     * Store new account with validation
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:mdm_chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:mdm_chart_of_accounts,id',
            'is_header' => 'boolean',
            'is_active' => 'boolean',
            'external_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        // Validate COA format
        if (!$this->validationService->validateCoaFormat($validated['code'])) {
            return back()
                ->withErrors(['code' => 'Format kode COA harus: X-XX-XX-XX-XXX'])
                ->withInput();
        }

        // Calculate level based on parent
        $level = 0;
        if (!empty($validated['parent_id'])) {
            $parent = MdmChartOfAccount::find($validated['parent_id']);
            $level = $parent->level + 1;
        }

        $validated['level'] = $level;
        $validated['is_header'] = $request->boolean('is_header');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        MdmChartOfAccount::create($validated);

        return redirect()
            ->route('mdm.coa.index')
            ->with('success', 'Chart of Account berhasil dibuat');
    }

    /**
     * Show edit form
     */
    public function edit(MdmChartOfAccount $account): View
    {
        $categories = ['asset', 'liability', 'equity', 'revenue', 'expense'];
        $normalBalances = ['debit', 'credit'];
        $parentAccounts = MdmChartOfAccount::active()
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('masterdatamanagement::coa.edit', compact('account', 'categories', 'normalBalances', 'parentAccounts'));
    }

    /**
     * Update account
     */
    public function update(Request $request, MdmChartOfAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:mdm_chart_of_accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:mdm_chart_of_accounts,id',
            'is_header' => 'boolean',
            'is_active' => 'boolean',
            'external_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        // Validate COA format
        if (!$this->validationService->validateCoaFormat($validated['code'])) {
            return back()
                ->withErrors(['code' => 'Format kode COA harus: X-XX-XX-XX-XXX'])
                ->withInput();
        }

        // Calculate level based on parent
        $level = 0;
        if (!empty($validated['parent_id'])) {
            $parent = MdmChartOfAccount::find($validated['parent_id']);
            $level = $parent->level + 1;
        }

        $validated['level'] = $level;
        $validated['is_header'] = $request->boolean('is_header');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = auth()->id();

        $account->update($validated);

        return redirect()
            ->route('mdm.coa.index')
            ->with('success', 'Chart of Account berhasil diupdate');
    }

    /**
     * Delete account
     */
    public function destroy(MdmChartOfAccount $account): RedirectResponse
    {
        if (!$this->validationService->canDelete($account)) {
            return back()->with('error', 'Akun tidak dapat dihapus karena memiliki sub-akun atau digunakan dalam transaksi');
        }

        $account->delete();

        return redirect()
            ->route('mdm.coa.index')
            ->with('success', 'Chart of Account berhasil dihapus');
    }

    /**
     * Export COA to Excel
     */
    public function export(): BinaryFileResponse
    {
        // TODO: Implement Excel export using Laravel Excel or similar
        // For now, return CSV
        $accounts = MdmChartOfAccount::orderBy('code')->get();

        $filename = 'coa_export_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Header
        fputcsv($handle, ['Code', 'Name', 'Category', 'Normal Balance', 'Parent Code', 'Level', 'Is Header', 'Is Active', 'External Code', 'Description']);

        // Data
        foreach ($accounts as $account) {
            fputcsv($handle, [
                $account->code,
                $account->name,
                $account->category,
                $account->normal_balance,
                $account->parent?->code ?? '',
                $account->level,
                $account->is_header ? 'Yes' : 'No',
                $account->is_active ? 'Yes' : 'No',
                $account->external_code ?? '',
                $account->description ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(
            fn() => print($csv),
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Import COA from Excel/CSV
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header
        fgetcsv($handle);

        DB::beginTransaction();
        try {
            $imported = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty($row[0])) {
                    continue;
                }

                // Validate format
                if (!$this->validationService->validateCoaFormat($row[0])) {
                    $errors[] = "Baris dengan kode {$row[0]}: Format tidak valid";
                    continue;
                }

                // Find parent by code if exists
                $parentId = null;
                if (!empty($row[4])) {
                    $parent = MdmChartOfAccount::where('code', $row[4])->first();
                    $parentId = $parent?->id;
                }

                MdmChartOfAccount::updateOrCreate(
                    ['code' => $row[0]],
                    [
                        'name' => $row[1],
                        'category' => $row[2],
                        'normal_balance' => $row[3],
                        'parent_id' => $parentId,
                        'level' => (int) $row[5],
                        'is_header' => strtolower($row[6]) === 'yes',
                        'is_active' => strtolower($row[7]) === 'yes',
                        'external_code' => $row[8] ?? null,
                        'description' => $row[9] ?? null,
                        'created_by' => auth()->id(),
                    ]
                );

                $imported++;
            }

            fclose($handle);
            DB::commit();

            $message = "Berhasil import {$imported} akun";
            if (!empty($errors)) {
                $message .= '. Errors: ' . implode(', ', $errors);
            }

            return redirect()
                ->route('mdm.coa.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);

            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
}
