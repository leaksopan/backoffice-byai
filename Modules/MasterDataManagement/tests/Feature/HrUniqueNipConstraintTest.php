<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmHumanResource;

/**
 * Feature: master-data-management, Property 7: Unique Code Constraint
 * 
 * For any master data entity type (funding source, service catalog, human resource, asset),
 * the system should reject creation or update if the code already exists within that entity type
 * 
 * Validates: Requirements 6.2
 */
class HrUniqueNipConstraintTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function property_unique_nip_constraint_prevents_duplicate_creation()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Generate random NIP
            $nip = 'NIP' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            // Create first HR with this NIP
            $hr1 = MdmHumanResource::create([
                'nip' => $nip,
                'name' => 'Employee ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position ' . $i,
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            // Try to create second HR with same NIP
            try {
                $hr2 = MdmHumanResource::create([
                    'nip' => $nip,
                    'name' => 'Employee Duplicate ' . $i,
                    'category' => $this->randomCategory(),
                    'position' => 'Position Duplicate ' . $i,
                    'employment_status' => $this->randomEmploymentStatus(),
                    'is_active' => true,
                ]);

                // If creation succeeded, it's a failure
                $failures[] = [
                    'iteration' => $i,
                    'nip' => $nip,
                    'reason' => 'Duplicate NIP was allowed',
                ];
            } catch (\Exception $e) {
                // Exception is expected - duplicate should be rejected
                // Check for various database error messages
                $message = $e->getMessage();
                $this->assertTrue(
                    str_contains($message, 'Duplicate') || 
                    str_contains($message, 'UNIQUE constraint') ||
                    str_contains($message, 'unique'),
                    'Expected unique constraint violation, got: ' . $message
                );
            }

            // Cleanup
            MdmHumanResource::where('nip', $nip)->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Duplicate NIPs were allowed. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_unique_nip_constraint_prevents_duplicate_update()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create two HRs with different NIPs
            $nip1 = 'NIP' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $nip2 = 'NIP' . str_pad(rand(1000000, 1999999), 6, '0', STR_PAD_LEFT);

            $hr1 = MdmHumanResource::create([
                'nip' => $nip1,
                'name' => 'Employee A ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position A',
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            $hr2 = MdmHumanResource::create([
                'nip' => $nip2,
                'name' => 'Employee B ' . $i,
                'category' => $this->randomCategory(),
                'position' => 'Position B',
                'employment_status' => $this->randomEmploymentStatus(),
                'is_active' => true,
            ]);

            // Try to update hr2 to use hr1's NIP
            try {
                $hr2->update(['nip' => $nip1]);

                // If update succeeded, it's a failure
                $failures[] = [
                    'iteration' => $i,
                    'nip' => $nip1,
                    'reason' => 'Update to duplicate NIP was allowed',
                ];
            } catch (\Exception $e) {
                // Exception is expected - duplicate should be rejected
                // Check for various database error messages
                $message = $e->getMessage();
                $this->assertTrue(
                    str_contains($message, 'Duplicate') || 
                    str_contains($message, 'UNIQUE constraint') ||
                    str_contains($message, 'unique'),
                    'Expected unique constraint violation, got: ' . $message
                );
            }

            // Cleanup
            MdmHumanResource::whereIn('nip', [$nip1, $nip2])->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Update to duplicate NIPs was allowed. Failures: ' . json_encode($failures)
        );
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
