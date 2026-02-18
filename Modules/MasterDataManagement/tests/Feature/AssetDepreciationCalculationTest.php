<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmAsset;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\AssetDepreciationService;
use Carbon\Carbon;

/**
 * Feature: master-data-management, Property 12: Asset Depreciation Calculation
 * 
 * For any asset with useful_life_years > 0 and depreciation_method specified,
 * the monthly depreciation amount should be calculated correctly according to the chosen method
 * (straight_line: (acquisition_value - residual_value) / (useful_life_years * 12),
 * declining_balance: book_value * rate / 12)
 * 
 * Validates: Requirements 7.3, 7.4
 */
class AssetDepreciationCalculationTest extends TestCase
{
    use RefreshDatabase;

    private AssetDepreciationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AssetDepreciationService();
    }

    /**
     * @test
     */
    public function property_straight_line_monthly_depreciation_is_correct()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Generate random asset parameters
            $acquisitionValue = rand(1000000, 10000000);
            $residualValue = rand(0, $acquisitionValue / 10);
            $usefulLifeYears = rand(1, 20);

            $location = MdmOrganizationUnit::create([
                'code' => 'LOC' . $i,
                'name' => 'Location ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => $acquisitionValue,
                'acquisition_date' => Carbon::now()->subMonths(rand(0, 60)),
                'useful_life_years' => $usefulLifeYears,
                'depreciation_method' => 'straight_line',
                'residual_value' => $residualValue,
                'current_location_id' => $location->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            // Calculate expected monthly depreciation
            $depreciableValue = $acquisitionValue - $residualValue;
            $totalMonths = $usefulLifeYears * 12;
            $expectedMonthly = round($depreciableValue / $totalMonths, 2);

            // Get actual monthly depreciation
            $actualMonthly = $this->service->calculateMonthlyDepreciation($asset);

            // Verify
            if (abs($expectedMonthly - $actualMonthly) > 0.01) {
                $failures[] = [
                    'iteration' => $i,
                    'acquisition_value' => $acquisitionValue,
                    'residual_value' => $residualValue,
                    'useful_life_years' => $usefulLifeYears,
                    'expected' => $expectedMonthly,
                    'actual' => $actualMonthly,
                    'difference' => abs($expectedMonthly - $actualMonthly),
                ];
            }

            // Cleanup
            $asset->delete();
            $location->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Straight line depreciation calculation incorrect. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_accumulated_depreciation_does_not_exceed_depreciable_value()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            $acquisitionValue = rand(1000000, 10000000);
            $residualValue = rand(0, $acquisitionValue / 10);
            $usefulLifeYears = rand(1, 10);

            $location = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 1000),
                'name' => 'Location ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $acquisitionDate = Carbon::now()->subYears(rand(0, $usefulLifeYears + 5));

            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 1000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => $acquisitionValue,
                'acquisition_date' => $acquisitionDate,
                'useful_life_years' => $usefulLifeYears,
                'depreciation_method' => 'straight_line',
                'residual_value' => $residualValue,
                'current_location_id' => $location->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            $depreciableValue = $acquisitionValue - $residualValue;
            $asOfDate = Carbon::now();

            $accumulated = $this->service->calculateAccumulatedDepreciation($asset, $asOfDate);

            // Accumulated should never exceed depreciable value
            if ($accumulated > $depreciableValue + 0.01) {
                $failures[] = [
                    'iteration' => $i,
                    'acquisition_value' => $acquisitionValue,
                    'residual_value' => $residualValue,
                    'depreciable_value' => $depreciableValue,
                    'accumulated' => $accumulated,
                    'excess' => $accumulated - $depreciableValue,
                ];
            }

            // Cleanup
            $asset->delete();
            $location->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Accumulated depreciation exceeded depreciable value. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_book_value_never_goes_below_residual_value()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            $acquisitionValue = rand(1000000, 10000000);
            $residualValue = rand(0, $acquisitionValue / 10);
            $usefulLifeYears = rand(1, 10);

            $location = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 2000),
                'name' => 'Location ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $acquisitionDate = Carbon::now()->subYears(rand(0, $usefulLifeYears + 10));

            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 2000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => $acquisitionValue,
                'acquisition_date' => $acquisitionDate,
                'useful_life_years' => $usefulLifeYears,
                'depreciation_method' => 'straight_line',
                'residual_value' => $residualValue,
                'current_location_id' => $location->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            $asOfDate = Carbon::now();
            $bookValue = $this->service->getBookValue($asset, $asOfDate);

            // Book value should never go below residual value
            if ($bookValue < $residualValue - 0.01) {
                $failures[] = [
                    'iteration' => $i,
                    'acquisition_value' => $acquisitionValue,
                    'residual_value' => $residualValue,
                    'book_value' => $bookValue,
                    'shortfall' => $residualValue - $bookValue,
                ];
            }

            // Cleanup
            $asset->delete();
            $location->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Book value went below residual value. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_declining_balance_depreciation_decreases_over_time()
    {
        $iterations = 50;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            $acquisitionValue = rand(1000000, 10000000);
            $residualValue = rand(0, $acquisitionValue / 10);
            $usefulLifeYears = rand(5, 15);

            $location = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 3000),
                'name' => 'Location ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $acquisitionDate = Carbon::now()->subMonths(24);

            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 3000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => $acquisitionValue,
                'acquisition_date' => $acquisitionDate,
                'useful_life_years' => $usefulLifeYears,
                'depreciation_method' => 'declining_balance',
                'residual_value' => $residualValue,
                'current_location_id' => $location->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            // Get accumulated depreciation at 12 months and 24 months
            $accumulated12 = $this->service->calculateAccumulatedDepreciation($asset, Carbon::now()->subMonths(12));
            $accumulated24 = $this->service->calculateAccumulatedDepreciation($asset, Carbon::now());

            // Accumulated should increase over time
            if ($accumulated24 <= $accumulated12) {
                $failures[] = [
                    'iteration' => $i,
                    'accumulated_12_months' => $accumulated12,
                    'accumulated_24_months' => $accumulated24,
                    'reason' => 'Accumulated depreciation did not increase',
                ];
            }

            // Cleanup
            $asset->delete();
            $location->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Declining balance depreciation did not increase over time. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_assets_without_depreciation_parameters_return_zero()
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            $location = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 4000),
                'name' => 'Location ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Asset without useful life or depreciation method
            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 4000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => 'tanah', // Land typically doesn't depreciate
                'acquisition_value' => rand(1000000, 10000000),
                'acquisition_date' => Carbon::now()->subYears(rand(1, 10)),
                'useful_life_years' => null,
                'depreciation_method' => null,
                'residual_value' => 0,
                'current_location_id' => $location->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            $monthly = $this->service->calculateMonthlyDepreciation($asset);
            $accumulated = $this->service->calculateAccumulatedDepreciation($asset, Carbon::now());

            $this->assertEquals(0, $monthly);
            $this->assertEquals(0, $accumulated);

            // Cleanup
            $asset->delete();
            $location->delete();
        }
    }

    private function randomCategory(): string
    {
        $categories = [
            'gedung',
            'peralatan_medis',
            'peralatan_non_medis',
            'kendaraan',
            'inventaris'
        ];
        return $categories[array_rand($categories)];
    }
}
