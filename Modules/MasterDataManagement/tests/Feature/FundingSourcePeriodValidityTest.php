<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Carbon\Carbon;

/**
 * Property Test: Period Validity Check
 * Feature: master-data-management, Property 8: Period Validity Check
 * Validates: Requirements 3.4
 */
class FundingSourcePeriodValidityTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Property: For any master data entity with a validity period (start_date, end_date),
     * the system should reject its use for transactions outside that period
     *
     * @test
     */
    public function property_rejects_usage_outside_validity_period()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runPeriodValidityTest();
        }
    }

    private function runPeriodValidityTest(): void
    {
        // Generate random funding source with validity period
        $startDate = Carbon::now()->subDays(rand(100, 365));
        $endDate = rand(0, 1) ? $startDate->copy()->addDays(rand(30, 365)) : null;
        
        $fundingSource = MdmFundingSource::create([
            'code' => 'FS' . uniqid(),
            'name' => 'Funding Source ' . uniqid(),
            'type' => ['apbn', 'pnbp', 'hibah'][array_rand(['apbn', 'pnbp', 'hibah'])],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
        ]);

        // Test 1: Date before start_date should be invalid
        $beforeStart = $startDate->copy()->subDays(rand(1, 30));
        $this->assertFalse(
            $fundingSource->isActiveOn($beforeStart),
            "Funding source should not be active before start_date"
        );

        // Test 2: Date on start_date should be valid
        $this->assertTrue(
            $fundingSource->isActiveOn($startDate),
            "Funding source should be active on start_date"
        );

        // Test 3: Date between start and end should be valid
        if ($endDate) {
            $daysBetween = $startDate->diffInDays($endDate);
            if ($daysBetween > 1) {
                $betweenDate = $startDate->copy()->addDays(rand(1, $daysBetween - 1));
                $this->assertTrue(
                    $fundingSource->isActiveOn($betweenDate),
                    "Funding source should be active between start and end dates"
                );
            }

            // Test 4: Date on end_date should be valid
            $this->assertTrue(
                $fundingSource->isActiveOn($endDate),
                "Funding source should be active on end_date"
            );

            // Test 5: Date after end_date should be invalid
            $afterEnd = $endDate->copy()->addDays(rand(1, 30));
            $this->assertFalse(
                $fundingSource->isActiveOn($afterEnd),
                "Funding source should not be active after end_date"
            );
        } else {
            // Test 6: If no end_date, any date after start should be valid
            $futureDate = $startDate->copy()->addDays(rand(1, 1000));
            $this->assertTrue(
                $fundingSource->isActiveOn($futureDate),
                "Funding source with no end_date should be active indefinitely"
            );
        }

        $fundingSource->delete();
    }

    /**
     * Test: Inactive funding source should never be valid regardless of period
     *
     * @test
     */
    public function property_inactive_funding_source_is_never_valid()
    {
        for ($i = 0; $i < 50; $i++) {
            $startDate = Carbon::now()->subDays(rand(100, 365));
            $endDate = $startDate->copy()->addDays(rand(30, 365));
            
            $fundingSource = MdmFundingSource::create([
                'code' => 'FS' . uniqid(),
                'name' => 'Inactive Funding Source ' . uniqid(),
                'type' => 'apbn',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => false, // Inactive
            ]);

            // Test dates within period but funding source is inactive
            $validDate = $startDate->copy()->addDays(rand(1, $startDate->diffInDays($endDate) - 1));
            $this->assertFalse(
                $fundingSource->isActiveOn($validDate),
                "Inactive funding source should never be valid even within period"
            );

            $fundingSource->delete();
        }
    }

    /**
     * Test: activeOn scope filters correctly
     *
     * @test
     */
    public function property_active_on_scope_filters_correctly()
    {
        // Create multiple funding sources with different periods
        $testDate = Carbon::now();
        
        // Valid: active and period covers test date
        $valid1 = MdmFundingSource::create([
            'code' => 'FS_VALID1',
            'name' => 'Valid 1',
            'type' => 'apbn',
            'start_date' => $testDate->copy()->subDays(10),
            'end_date' => $testDate->copy()->addDays(10),
            'is_active' => true,
        ]);

        // Valid: active and no end date
        $valid2 = MdmFundingSource::create([
            'code' => 'FS_VALID2',
            'name' => 'Valid 2',
            'type' => 'pnbp',
            'start_date' => $testDate->copy()->subDays(10),
            'end_date' => null,
            'is_active' => true,
        ]);

        // Invalid: inactive
        $invalid1 = MdmFundingSource::create([
            'code' => 'FS_INVALID1',
            'name' => 'Invalid 1',
            'type' => 'hibah',
            'start_date' => $testDate->copy()->subDays(10),
            'end_date' => $testDate->copy()->addDays(10),
            'is_active' => false,
        ]);

        // Invalid: before start date
        $invalid2 = MdmFundingSource::create([
            'code' => 'FS_INVALID2',
            'name' => 'Invalid 2',
            'type' => 'apbn',
            'start_date' => $testDate->copy()->addDays(5),
            'end_date' => $testDate->copy()->addDays(15),
            'is_active' => true,
        ]);

        // Invalid: after end date
        $invalid3 = MdmFundingSource::create([
            'code' => 'FS_INVALID3',
            'name' => 'Invalid 3',
            'type' => 'pnbp',
            'start_date' => $testDate->copy()->subDays(20),
            'end_date' => $testDate->copy()->subDays(5),
            'is_active' => true,
        ]);

        // Query using activeOn scope
        $activeSources = MdmFundingSource::activeOn($testDate)->get();

        // Should only return valid1 and valid2
        $this->assertCount(2, $activeSources);
        $this->assertTrue($activeSources->contains('id', $valid1->id));
        $this->assertTrue($activeSources->contains('id', $valid2->id));
        $this->assertFalse($activeSources->contains('id', $invalid1->id));
        $this->assertFalse($activeSources->contains('id', $invalid2->id));
        $this->assertFalse($activeSources->contains('id', $invalid3->id));

        // Cleanup
        $valid1->delete();
        $valid2->delete();
        $invalid1->delete();
        $invalid2->delete();
        $invalid3->delete();
    }

    /**
     * Test edge case: boundary dates (start_date and end_date)
     *
     * @test
     */
    public function property_boundary_dates_are_inclusive()
    {
        for ($i = 0; $i < 20; $i++) {
            $startDate = Carbon::now()->subDays(rand(10, 100));
            $endDate = $startDate->copy()->addDays(rand(30, 100));
            
            $fundingSource = MdmFundingSource::create([
                'code' => 'FS' . uniqid(),
                'name' => 'Boundary Test ' . uniqid(),
                'type' => 'apbn',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => true,
            ]);

            // Exact start date should be valid
            $this->assertTrue(
                $fundingSource->isActiveOn($startDate),
                "Start date should be inclusive"
            );

            // Exact end date should be valid
            $this->assertTrue(
                $fundingSource->isActiveOn($endDate),
                "End date should be inclusive"
            );

            // One day before start should be invalid
            $this->assertFalse(
                $fundingSource->isActiveOn($startDate->copy()->subDay()),
                "Day before start should be invalid"
            );

            // One day after end should be invalid
            $this->assertFalse(
                $fundingSource->isActiveOn($endDate->copy()->addDay()),
                "Day after end should be invalid"
            );

            $fundingSource->delete();
        }
    }
}
