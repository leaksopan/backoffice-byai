<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmHumanResource;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmHrAssignment;
use Carbon\Carbon;

/**
 * Feature: master-data-management, Property 11: HR Allocation Percentage Limit
 * 
 * For any human resource, the sum of allocation_percentage across all active assignments
 * should not exceed 100%
 * 
 * Validates: Requirements 6.6
 */
class HrAllocationPercentageLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function property_total_allocation_cannot_exceed_100_percent()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create HR
            $hr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            // Create units
            $unit1 = MdmOrganizationUnit::create([
                'code' => 'UNIT' . $i . 'A',
                'name' => 'Unit A ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $unit2 = MdmOrganizationUnit::create([
                'code' => 'UNIT' . $i . 'B',
                'name' => 'Unit B ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create first assignment with random percentage (50-80%)
            $firstAllocation = rand(50, 80);
            MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit1->id,
                'allocation_percentage' => $firstAllocation,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => null,
                'is_active' => true,
            ]);

            // Try to create second assignment that would exceed 100%
            $secondAllocation = rand(30, 60); // This might exceed 100%
            $totalAllocation = $firstAllocation + $secondAllocation;

            if ($totalAllocation > 100) {
                // Should be rejected
                try {
                    MdmHrAssignment::create([
                        'hr_id' => $hr->id,
                        'unit_id' => $unit2->id,
                        'allocation_percentage' => $secondAllocation,
                        'start_date' => Carbon::now()->subDays(5),
                        'end_date' => null,
                        'is_active' => true,
                    ]);

                    // Check if total exceeds 100%
                    $actualTotal = $hr->fresh()->activeAssignments()->sum('allocation_percentage');
                    if ($actualTotal > 100) {
                        $failures[] = [
                            'iteration' => $i,
                            'first_allocation' => $firstAllocation,
                            'second_allocation' => $secondAllocation,
                            'total' => $actualTotal,
                            'reason' => 'Allocation exceeding 100% was allowed',
                        ];
                    }
                } catch (\Exception $e) {
                    // Exception is acceptable - validation prevented over-allocation
                }
            } else {
                // Should be allowed
                MdmHrAssignment::create([
                    'hr_id' => $hr->id,
                    'unit_id' => $unit2->id,
                    'allocation_percentage' => $secondAllocation,
                    'start_date' => Carbon::now()->subDays(5),
                    'end_date' => null,
                    'is_active' => true,
                ]);

                $actualTotal = $hr->fresh()->activeAssignments()->sum('allocation_percentage');
                $this->assertLessThanOrEqual(100, $actualTotal);
            }

            // Cleanup
            $hr->assignments()->delete();
            $hr->delete();
            $unit1->delete();
            $unit2->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Allocations exceeding 100% were allowed. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_inactive_assignments_do_not_count_toward_limit()
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            // Create HR
            $hr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i + 1000, 6, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            // Create units
            $unit1 = MdmOrganizationUnit::create([
                'code' => 'UNIT' . ($i + 1000) . 'A',
                'name' => 'Unit A ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $unit2 = MdmOrganizationUnit::create([
                'code' => 'UNIT' . ($i + 1000) . 'B',
                'name' => 'Unit B ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create inactive assignment with 80%
            MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit1->id,
                'allocation_percentage' => 80,
                'start_date' => Carbon::now()->subDays(100),
                'end_date' => Carbon::now()->subDays(50),
                'is_active' => false,
            ]);

            // Create active assignment with 70% - should be allowed
            MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit2->id,
                'allocation_percentage' => 70,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => null,
                'is_active' => true,
            ]);

            // Verify only active assignments count
            $activeTotal = $hr->fresh()->activeAssignments()->sum('allocation_percentage');
            $this->assertEquals(70, $activeTotal);
            $this->assertLessThanOrEqual(100, $activeTotal);

            // Cleanup
            $hr->assignments()->delete();
            $hr->delete();
            $unit1->delete();
            $unit2->delete();
        }
    }

    /**
     * @test
     */
    public function property_expired_assignments_do_not_count_toward_limit()
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            // Create HR
            $hr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i + 2000, 6, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            // Create units
            $unit1 = MdmOrganizationUnit::create([
                'code' => 'UNIT' . ($i + 2000) . 'A',
                'name' => 'Unit A ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $unit2 = MdmOrganizationUnit::create([
                'code' => 'UNIT' . ($i + 2000) . 'B',
                'name' => 'Unit B ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create expired assignment with 60%
            MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit1->id,
                'allocation_percentage' => 60,
                'start_date' => Carbon::now()->subDays(100),
                'end_date' => Carbon::now()->subDays(1),
                'is_active' => true,
            ]);

            // Create current assignment with 80% - should be allowed
            MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit2->id,
                'allocation_percentage' => 80,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => null,
                'is_active' => true,
            ]);

            // Verify only current assignments count
            $activeTotal = $hr->fresh()->activeAssignments()->sum('allocation_percentage');
            $this->assertEquals(80, $activeTotal);
            $this->assertLessThanOrEqual(100, $activeTotal);

            // Cleanup
            $hr->assignments()->delete();
            $hr->delete();
            $unit1->delete();
            $unit2->delete();
        }
    }

    private function randomCategory(): string
    {
        $categories = [
            'medis_dokter',
            'medis_perawat',
            'medis_bidan',
            'penunjang_medis',
            'administrasi',
            'umum'
        ];
        return $categories[array_rand($categories)];
    }

    private function randomEmploymentStatus(): string
    {
        $statuses = ['pns', 'pppk', 'kontrak', 'honorer'];
        return $statuses[array_rand($statuses)];
    }
}
