<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Carbon\Carbon;

/**
 * Property Test: Inactive Entity Prevention (Funding Sources)
 * Feature: master-data-management, Property 4: Inactive Entity Prevention
 * Validates: Requirements 3.6
 */
class FundingSourceInactivePreventionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Property: For any master data entity with is_active=false,
     * the system should reject its use in new transactions or assignments
     *
     * @test
     */
    public function property_inactive_funding_sources_cannot_be_used()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runInactivePreventionTest();
        }
    }

    private function runInactivePreventionTest(): void
    {
        // Generate random funding source
        $startDate = Carbon::now()->subDays(rand(100, 365));
        $endDate = rand(0, 1) ? $startDate->copy()->addDays(rand(30, 365)) : null;
        $isActive = rand(0, 1) === 1;
        
        $fundingSource = MdmFundingSource::create([
            'code' => 'FS' . uniqid(),
            'name' => 'Funding Source ' . uniqid(),
            'type' => ['apbn', 'pnbp', 'hibah'][array_rand(['apbn', 'pnbp', 'hibah'])],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $isActive,
        ]);

        // Test: Check if funding source can be used on a valid date
        $validDate = $startDate->copy()->addDays(rand(1, 10));
        
        if ($isActive) {
            // Active funding source should be usable within period
            $this->assertTrue(
                $fundingSource->isActiveOn($validDate),
                "Active funding source should be usable within period"
            );
        } else {
            // Inactive funding source should NOT be usable even within period
            $this->assertFalse(
                $fundingSource->isActiveOn($validDate),
                "Inactive funding source should not be usable even within valid period"
            );
        }

        // Test: Active scope should only return active funding sources
        $activeSources = MdmFundingSource::active()->get();
        
        if ($isActive) {
            $this->assertTrue(
                $activeSources->contains('id', $fundingSource->id),
                "Active scope should include active funding sources"
            );
        } else {
            $this->assertFalse(
                $activeSources->contains('id', $fundingSource->id),
                "Active scope should exclude inactive funding sources"
            );
        }

        $fundingSource->delete();
    }

    /**
     * Test: Deactivating a funding source prevents future use
     *
     * @test
     */
    public function property_deactivating_funding_source_prevents_use()
    {
        for ($i = 0; $i < 50; $i++) {
            $fundingSource = MdmFundingSource::create([
                'code' => 'FS' . uniqid(),
                'name' => 'Active Source ' . uniqid(),
                'type' => 'apbn',
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(10),
                'is_active' => true,
            ]);

            $testDate = Carbon::now();

            // Initially should be usable
            $this->assertTrue(
                $fundingSource->isActiveOn($testDate),
                "Initially active funding source should be usable"
            );

            // Deactivate
            $fundingSource->update(['is_active' => false]);

            // Should no longer be usable
            $this->assertFalse(
                $fundingSource->fresh()->isActiveOn($testDate),
                "Deactivated funding source should not be usable"
            );

            $fundingSource->delete();
        }
    }

    /**
     * Test: activeOn scope filters out inactive sources
     *
     * @test
     */
    public function property_active_on_scope_excludes_inactive_sources()
    {
        $testDate = Carbon::now();
        
        // Create active funding source
        $active = MdmFundingSource::create([
            'code' => 'FS_ACTIVE',
            'name' => 'Active Source',
            'type' => 'apbn',
            'start_date' => $testDate->copy()->subDays(10),
            'end_date' => $testDate->copy()->addDays(10),
            'is_active' => true,
        ]);

        // Create inactive funding source with valid period
        $inactive = MdmFundingSource::create([
            'code' => 'FS_INACTIVE',
            'name' => 'Inactive Source',
            'type' => 'pnbp',
            'start_date' => $testDate->copy()->subDays(10),
            'end_date' => $testDate->copy()->addDays(10),
            'is_active' => false,
        ]);

        // Query using activeOn scope
        $activeSources = MdmFundingSource::activeOn($testDate)->get();

        // Should only return active source
        $this->assertCount(1, $activeSources);
        $this->assertTrue($activeSources->contains('id', $active->id));
        $this->assertFalse($activeSources->contains('id', $inactive->id));

        // Cleanup
        $active->delete();
        $inactive->delete();
    }

    /**
     * Test: Reactivating a funding source allows use again
     *
     * @test
     */
    public function property_reactivating_funding_source_allows_use()
    {
        for ($i = 0; $i < 20; $i++) {
            $fundingSource = MdmFundingSource::create([
                'code' => 'FS' . uniqid(),
                'name' => 'Reactivation Test ' . uniqid(),
                'type' => 'hibah',
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(10),
                'is_active' => false, // Start inactive
            ]);

            $testDate = Carbon::now();

            // Should not be usable when inactive
            $this->assertFalse(
                $fundingSource->isActiveOn($testDate),
                "Inactive funding source should not be usable"
            );

            // Reactivate
            $fundingSource->update(['is_active' => true]);

            // Should now be usable
            $this->assertTrue(
                $fundingSource->fresh()->isActiveOn($testDate),
                "Reactivated funding source should be usable"
            );

            $fundingSource->delete();
        }
    }

    /**
     * Test: Multiple inactive sources are all excluded
     *
     * @test
     */
    public function property_all_inactive_sources_are_excluded()
    {
        $testDate = Carbon::now();
        $inactiveSources = [];
        
        // Create multiple inactive funding sources
        for ($i = 0; $i < 10; $i++) {
            $source = MdmFundingSource::create([
                'code' => 'FS_INACTIVE_' . $i,
                'name' => 'Inactive Source ' . $i,
                'type' => ['apbn', 'pnbp', 'hibah'][array_rand(['apbn', 'pnbp', 'hibah'])],
                'start_date' => $testDate->copy()->subDays(10),
                'end_date' => $testDate->copy()->addDays(10),
                'is_active' => false,
            ]);
            $inactiveSources[] = $source;
        }

        // Query active sources
        $activeSources = MdmFundingSource::active()->get();

        // None of the inactive sources should be in the result
        foreach ($inactiveSources as $source) {
            $this->assertFalse(
                $activeSources->contains('id', $source->id),
                "Inactive source {$source->code} should not be in active scope"
            );
        }

        // Cleanup
        foreach ($inactiveSources as $source) {
            $source->delete();
        }
    }

    /**
     * Test edge case: Inactive status overrides valid period
     *
     * @test
     */
    public function property_inactive_status_overrides_valid_period()
    {
        for ($i = 0; $i < 30; $i++) {
            // Create funding source with very long valid period
            $fundingSource = MdmFundingSource::create([
                'code' => 'FS' . uniqid(),
                'name' => 'Long Period Source ' . uniqid(),
                'type' => 'apbn',
                'start_date' => Carbon::now()->subYears(5),
                'end_date' => Carbon::now()->addYears(5),
                'is_active' => false, // Inactive
            ]);

            // Test various dates within the period
            $dates = [
                Carbon::now()->subYears(2),
                Carbon::now()->subYear(),
                Carbon::now(),
                Carbon::now()->addYear(),
                Carbon::now()->addYears(2),
            ];

            foreach ($dates as $date) {
                $this->assertFalse(
                    $fundingSource->isActiveOn($date),
                    "Inactive funding source should not be usable on any date, even within valid period"
                );
            }

            $fundingSource->delete();
        }
    }
}
