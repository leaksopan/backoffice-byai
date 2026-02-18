<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmHumanResource;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmHrAssignment;
use Carbon\Carbon;

/**
 * Feature: master-data-management, Property 4: Inactive Entity Prevention
 * 
 * For any master data entity with is_active=false, the system should reject
 * its use in new transactions or assignments
 * 
 * Validates: Requirements 6.7
 */
class HrInactivePreventionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function property_inactive_hr_cannot_receive_new_assignments()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create inactive HR
            $hr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => false, // Inactive
            ]);

            // Create unit
            $unit = MdmOrganizationUnit::create([
                'code' => 'UNIT' . $i,
                'name' => 'Unit ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Try to create assignment for inactive HR
            try {
                $assignment = MdmHrAssignment::create([
                    'hr_id' => $hr->id,
                    'unit_id' => $unit->id,
                    'allocation_percentage' => rand(20, 80),
                    'start_date' => Carbon::now(),
                    'end_date' => null,
                    'is_active' => true,
                ]);

                // If assignment was created, check if HR is inactive
                if ($assignment->exists && !$hr->is_active) {
                    $failures[] = [
                        'iteration' => $i,
                        'hr_nip' => $hr->nip,
                        'hr_is_active' => $hr->is_active,
                        'reason' => 'Assignment created for inactive HR',
                    ];
                }
            } catch (\Exception $e) {
                // Exception is acceptable - validation prevented assignment
            }

            // Cleanup
            $hr->assignments()->delete();
            $hr->delete();
            $unit->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Inactive HRs received new assignments. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_deactivating_hr_prevents_new_assignments()
    {
        $iterations = 50;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create active HR
            $hr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i + 1000, 6, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            // Create unit
            $unit = MdmOrganizationUnit::create([
                'code' => 'UNIT' . ($i + 1000),
                'name' => 'Unit ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create initial assignment (should succeed)
            MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit->id,
                'allocation_percentage' => 50,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => null,
                'is_active' => true,
            ]);

            // Deactivate HR
            $hr->update(['is_active' => false]);

            // Try to create new assignment after deactivation
            try {
                $newAssignment = MdmHrAssignment::create([
                    'hr_id' => $hr->id,
                    'unit_id' => $unit->id,
                    'allocation_percentage' => 30,
                    'start_date' => Carbon::now(),
                    'end_date' => null,
                    'is_active' => true,
                ]);

                // If assignment was created, it's a failure
                if ($newAssignment->exists) {
                    $failures[] = [
                        'iteration' => $i,
                        'hr_nip' => $hr->nip,
                        'reason' => 'New assignment created after HR deactivation',
                    ];
                }
            } catch (\Exception $e) {
                // Exception is acceptable - validation prevented assignment
            }

            // Cleanup
            $hr->assignments()->delete();
            $hr->delete();
            $unit->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: New assignments created after HR deactivation. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_active_scope_excludes_inactive_hr()
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            // Create mix of active and inactive HRs
            $activeHr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i + 2000, 6, '0', STR_PAD_LEFT),
                'name' => 'Active Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            $inactiveHr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i + 3000, 6, '0', STR_PAD_LEFT),
                'name' => 'Inactive Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => false,
            ]);

            // Query using active scope
            $activeHrs = MdmHumanResource::active()->get();

            // Verify inactive HR is not in results
            $this->assertTrue($activeHrs->contains($activeHr));
            $this->assertFalse($activeHrs->contains($inactiveHr));

            // Cleanup
            $activeHr->delete();
            $inactiveHr->delete();
        }
    }

    /**
     * @test
     */
    public function property_reactivating_hr_allows_new_assignments()
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            // Create inactive HR
            $hr = MdmHumanResource::create([
                'nip' => 'NIP' . str_pad($i + 4000, 6, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => false,
            ]);

            // Create unit
            $unit = MdmOrganizationUnit::create([
                'code' => 'UNIT' . ($i + 4000),
                'name' => 'Unit ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Reactivate HR
            $hr->update(['is_active' => true]);

            // Try to create assignment (should succeed now)
            $assignment = MdmHrAssignment::create([
                'hr_id' => $hr->id,
                'unit_id' => $unit->id,
                'allocation_percentage' => 60,
                'start_date' => Carbon::now(),
                'end_date' => null,
                'is_active' => true,
            ]);

            // Verify assignment was created
            $this->assertTrue($assignment->exists);
            $this->assertEquals($hr->id, $assignment->hr_id);

            // Cleanup
            $hr->assignments()->delete();
            $hr->delete();
            $unit->delete();
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
