<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Services\DirectCostAssignmentService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmHumanResource;
use Modules\MasterDataManagement\Models\MdmHrAssignment;
use Modules\MasterDataManagement\Models\MdmAsset;

/**
 * Unit Tests untuk Direct Cost Assignment
 * 
 * Validates: Requirements 5.3, 5.4
 */
class DirectCostAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected DirectCostAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->service = new DirectCostAssignmentService();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    /**
     * Test gaji allocation berdasarkan HR assignment percentage
     * 
     * @test
     * Validates: Requirements 5.3
     */
    public function test_salary_allocation_based_on_hr_assignment_percentage()
    {
        // Setup: Create organization units
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'OU-001',
            'name' => 'Unit A',
            'type' => 'department',
            'is_active' => true,
        ]);

        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'OU-002',
            'name' => 'Unit B',
            'type' => 'department',
            'is_active' => true,
        ]);

        // Create cost centers
        $costCenter1 = CostCenter::create([
            'code' => 'CC-001',
            'name' => 'Cost Center A',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        $costCenter2 = CostCenter::create([
            'code' => 'CC-002',
            'name' => 'Cost Center B',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        // Create HR
        $hr = MdmHumanResource::create([
            'nip' => 'NIP-001',
            'code' => 'HR-001',
            'name' => 'John Doe',
            'category' => 'medis_dokter',
            'position' => 'Doctor',
            'employment_status' => 'pns',
            'is_active' => true,
        ]);

        // Create HR assignments: 60% to Unit A, 40% to Unit B
        MdmHrAssignment::create([
            'hr_id' => $hr->id,
            'unit_id' => $orgUnit1->id,
            'allocation_percentage' => 60.00,
            'start_date' => Carbon::now()->subMonth(),
            'is_active' => true,
        ]);

        MdmHrAssignment::create([
            'hr_id' => $hr->id,
            'unit_id' => $orgUnit2->id,
            'allocation_percentage' => 40.00,
            'start_date' => Carbon::now()->subMonth(),
            'is_active' => true,
        ]);

        // Execute: Assign salary of 10,000,000
        $salaryAmount = 10000000.00;
        $transactions = $this->service->assignSalaryCost(
            $hr->id,
            $salaryAmount,
            Carbon::now(),
            'Gaji bulan ini'
        );

        // Assert: Should create 2 transactions
        $this->assertCount(2, $transactions);

        // Assert: First transaction (60%)
        $this->assertEquals($costCenter1->id, $transactions[0]->cost_center_id);
        $this->assertEquals(6000000.00, $transactions[0]->amount);
        $this->assertEquals('direct_cost', $transactions[0]->transaction_type);
        $this->assertEquals('personnel', $transactions[0]->category);
        $this->assertEquals('salary', $transactions[0]->reference_type);
        $this->assertEquals($hr->id, $transactions[0]->reference_id);

        // Assert: Second transaction (40%)
        $this->assertEquals($costCenter2->id, $transactions[1]->cost_center_id);
        $this->assertEquals(4000000.00, $transactions[1]->amount);
        $this->assertEquals('direct_cost', $transactions[1]->transaction_type);
        $this->assertEquals('personnel', $transactions[1]->category);

        // Assert: Total should equal original salary
        $totalAllocated = $transactions[0]->amount + $transactions[1]->amount;
        $this->assertEquals($salaryAmount, $totalAllocated);

        // Assert: Transactions are persisted
        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id' => $costCenter1->id,
            'amount' => 6000000.00,
            'reference_type' => 'salary',
            'reference_id' => $hr->id,
        ]);

        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id' => $costCenter2->id,
            'amount' => 4000000.00,
            'reference_type' => 'salary',
            'reference_id' => $hr->id,
        ]);
    }

    /**
     * Test salary allocation with single assignment (100%)
     * 
     * @test
     */
    public function test_salary_allocation_with_single_assignment()
    {
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU-003',
            'name' => 'Unit C',
            'type' => 'department',
            'is_active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC-003',
            'name' => 'Cost Center C',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        $hr = MdmHumanResource::create([
            'nip' => 'NIP-002',
            'code' => 'HR-002',
            'name' => 'Jane Smith',
            'category' => 'medis_perawat',
            'position' => 'Nurse',
            'employment_status' => 'pns',
            'is_active' => true,
        ]);

        MdmHrAssignment::create([
            'hr_id' => $hr->id,
            'unit_id' => $orgUnit->id,
            'allocation_percentage' => 100.00,
            'start_date' => Carbon::now()->subMonth(),
            'is_active' => true,
        ]);

        $salaryAmount = 5000000.00;
        $transactions = $this->service->assignSalaryCost(
            $hr->id,
            $salaryAmount,
            Carbon::now()
        );

        $this->assertCount(1, $transactions);
        $this->assertEquals($costCenter->id, $transactions[0]->cost_center_id);
        $this->assertEquals($salaryAmount, $transactions[0]->amount);
    }

    /**
     * Test salary allocation fails when no active assignments
     * 
     * @test
     */
    public function test_salary_allocation_fails_when_no_active_assignments()
    {
        $hr = MdmHumanResource::create([
            'nip' => 'NIP-003',
            'code' => 'HR-003',
            'name' => 'Bob Johnson',
            'category' => 'administrasi',
            'position' => 'Admin',
            'employment_status' => 'kontrak',
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tidak ada penugasan aktif');

        $this->service->assignSalaryCost(
            $hr->id,
            5000000.00,
            Carbon::now()
        );
    }

    /**
     * Test depresiasi allocation berdasarkan asset location
     * 
     * @test
     * Validates: Requirements 5.4
     */
    public function test_depreciation_allocation_based_on_asset_location()
    {
        // Setup: Create organization unit and cost center
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU-004',
            'name' => 'Unit D',
            'type' => 'department',
            'is_active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC-004',
            'name' => 'Cost Center D',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        // Create asset at location
        $asset = MdmAsset::create([
            'code' => 'AST-001',
            'name' => 'Medical Equipment',
            'category' => 'peralatan_medis',
            'acquisition_value' => 100000000.00,
            'acquisition_date' => Carbon::now()->subYear(),
            'useful_life_years' => 10,
            'depreciation_method' => 'straight_line',
            'residual_value' => 10000000.00,
            'current_location_id' => $orgUnit->id,
            'condition' => 'baik',
            'is_active' => true,
        ]);

        // Execute: Assign depreciation
        $depreciationAmount = 750000.00; // Monthly depreciation
        $transaction = $this->service->assignDepreciationCost(
            $asset->id,
            $depreciationAmount,
            Carbon::now(),
            'Depresiasi bulanan'
        );

        // Assert: Transaction created correctly
        $this->assertNotNull($transaction->id);
        $this->assertEquals($costCenter->id, $transaction->cost_center_id);
        $this->assertEquals($depreciationAmount, $transaction->amount);
        $this->assertEquals('direct_cost', $transaction->transaction_type);
        $this->assertEquals('depreciation', $transaction->category);
        $this->assertEquals('depreciation', $transaction->reference_type);
        $this->assertEquals($asset->id, $transaction->reference_id);

        // Assert: Transaction is persisted
        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id' => $costCenter->id,
            'amount' => $depreciationAmount,
            'reference_type' => 'depreciation',
            'reference_id' => $asset->id,
        ]);
    }

    /**
     * Test depreciation allocation fails when asset has no location
     * 
     * @test
     */
    public function test_depreciation_allocation_fails_when_asset_has_no_location()
    {
        $asset = MdmAsset::create([
            'code' => 'AST-002',
            'name' => 'Equipment Without Location',
            'category' => 'peralatan_medis',
            'acquisition_value' => 50000000.00,
            'acquisition_date' => Carbon::now()->subYear(),
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'current_location_id' => null, // No location
            'condition' => 'baik',
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('tidak memiliki lokasi');

        $this->service->assignDepreciationCost(
            $asset->id,
            500000.00,
            Carbon::now()
        );
    }

    /**
     * Test depreciation allocation fails when asset is inactive
     * 
     * @test
     */
    public function test_depreciation_allocation_fails_when_asset_is_inactive()
    {
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU-005',
            'name' => 'Unit E',
            'type' => 'department',
            'is_active' => true,
        ]);

        $asset = MdmAsset::create([
            'code' => 'AST-003',
            'name' => 'Inactive Equipment',
            'category' => 'peralatan_medis',
            'acquisition_value' => 30000000.00,
            'acquisition_date' => Carbon::now()->subYears(2),
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'current_location_id' => $orgUnit->id,
            'condition' => 'rusak_berat',
            'is_active' => false, // Inactive
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('tidak aktif');

        $this->service->assignDepreciationCost(
            $asset->id,
            300000.00,
            Carbon::now()
        );
    }

    /**
     * Test material cost assignment
     * 
     * @test
     */
    public function test_material_cost_assignment()
    {
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU-006',
            'name' => 'Unit F',
            'type' => 'department',
            'is_active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC-006',
            'name' => 'Cost Center F',
            'type' => 'non_medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        $amount = 2500000.00;
        $transaction = $this->service->assignMaterialCost(
            $costCenter->id,
            $amount,
            Carbon::now(),
            'purchase',
            123,
            'Pembelian obat-obatan'
        );

        $this->assertNotNull($transaction->id);
        $this->assertEquals($costCenter->id, $transaction->cost_center_id);
        $this->assertEquals($amount, $transaction->amount);
        $this->assertEquals('direct_cost', $transaction->transaction_type);
        $this->assertEquals('supplies', $transaction->category);
        $this->assertEquals('purchase', $transaction->reference_type);
        $this->assertEquals(123, $transaction->reference_id);
    }

    /**
     * Test rounding in salary allocation
     * 
     * @test
     */
    public function test_salary_allocation_handles_rounding_correctly()
    {
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'OU-007',
            'name' => 'Unit G',
            'type' => 'department',
            'is_active' => true,
        ]);

        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'OU-008',
            'name' => 'Unit H',
            'type' => 'department',
            'is_active' => true,
        ]);

        $orgUnit3 = MdmOrganizationUnit::create([
            'code' => 'OU-009',
            'name' => 'Unit I',
            'type' => 'department',
            'is_active' => true,
        ]);

        CostCenter::create([
            'code' => 'CC-007',
            'name' => 'Cost Center G',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        CostCenter::create([
            'code' => 'CC-008',
            'name' => 'Cost Center H',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        CostCenter::create([
            'code' => 'CC-009',
            'name' => 'Cost Center I',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit3->id,
            'is_active' => true,
            'effective_date' => Carbon::now(),
        ]);

        $hr = MdmHumanResource::create([
            'nip' => 'NIP-004',
            'code' => 'HR-004',
            'name' => 'Alice Brown',
            'category' => 'medis_dokter',
            'position' => 'Specialist',
            'employment_status' => 'pns',
            'is_active' => true,
        ]);

        // Create assignments with percentages that don't divide evenly: 33.33%, 33.33%, 33.34%
        MdmHrAssignment::create([
            'hr_id' => $hr->id,
            'unit_id' => $orgUnit1->id,
            'allocation_percentage' => 33.33,
            'start_date' => Carbon::now()->subMonth(),
            'is_active' => true,
        ]);

        MdmHrAssignment::create([
            'hr_id' => $hr->id,
            'unit_id' => $orgUnit2->id,
            'allocation_percentage' => 33.33,
            'start_date' => Carbon::now()->subMonth(),
            'is_active' => true,
        ]);

        MdmHrAssignment::create([
            'hr_id' => $hr->id,
            'unit_id' => $orgUnit3->id,
            'allocation_percentage' => 33.34,
            'start_date' => Carbon::now()->subMonth(),
            'is_active' => true,
        ]);

        $salaryAmount = 10000000.00;
        $transactions = $this->service->assignSalaryCost(
            $hr->id,
            $salaryAmount,
            Carbon::now()
        );

        $this->assertCount(3, $transactions);

        // Check amounts are rounded to 2 decimal places
        $this->assertEquals(3333000.00, $transactions[0]->amount);
        $this->assertEquals(3333000.00, $transactions[1]->amount);
        $this->assertEquals(3334000.00, $transactions[2]->amount);

        // Total should be close to original (within rounding tolerance)
        $totalAllocated = array_sum(array_map(fn($t) => $t->amount, $transactions));
        $this->assertEquals($salaryAmount, $totalAllocated);
    }
}

