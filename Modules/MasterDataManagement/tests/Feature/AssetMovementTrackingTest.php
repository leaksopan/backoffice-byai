<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmAsset;
use Modules\MasterDataManagement\Models\MdmAssetMovement;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Carbon\Carbon;

/**
 * Feature: master-data-management, Property 13: Asset Movement Tracking
 * 
 * For any asset movement, the system should create a record in mdm_asset_movements
 * with from_location_id (previous location), to_location_id (new location),
 * and update the asset's current_location_id
 * 
 * Validates: Requirements 7.6
 */
class AssetMovementTrackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function property_asset_movement_creates_tracking_record()
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create locations
            $location1 = MdmOrganizationUnit::create([
                'code' => 'LOC' . $i . 'A',
                'name' => 'Location A ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $location2 = MdmOrganizationUnit::create([
                'code' => 'LOC' . $i . 'B',
                'name' => 'Location B ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create asset at location1
            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => rand(1000000, 10000000),
                'acquisition_date' => Carbon::now()->subMonths(rand(1, 60)),
                'current_location_id' => $location1->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            $originalLocationId = $asset->current_location_id;

            // Create movement record
            $movementDate = Carbon::now()->subDays(rand(1, 30));
            $movement = MdmAssetMovement::create([
                'asset_id' => $asset->id,
                'from_location_id' => $originalLocationId,
                'to_location_id' => $location2->id,
                'movement_date' => $movementDate,
                'reason' => 'Test movement ' . $i,
                'created_by' => 1,
            ]);

            // Update asset location
            $asset->update(['current_location_id' => $location2->id]);

            // Verify movement record was created
            if (!$movement->exists) {
                $failures[] = [
                    'iteration' => $i,
                    'reason' => 'Movement record was not created',
                ];
            }

            // Verify movement has correct from_location_id
            if ($movement->from_location_id !== $originalLocationId) {
                $failures[] = [
                    'iteration' => $i,
                    'expected_from' => $originalLocationId,
                    'actual_from' => $movement->from_location_id,
                    'reason' => 'from_location_id mismatch',
                ];
            }

            // Verify movement has correct to_location_id
            if ($movement->to_location_id !== $location2->id) {
                $failures[] = [
                    'iteration' => $i,
                    'expected_to' => $location2->id,
                    'actual_to' => $movement->to_location_id,
                    'reason' => 'to_location_id mismatch',
                ];
            }

            // Verify asset's current_location_id was updated
            $asset->refresh();
            if ($asset->current_location_id !== $location2->id) {
                $failures[] = [
                    'iteration' => $i,
                    'expected_current' => $location2->id,
                    'actual_current' => $asset->current_location_id,
                    'reason' => 'Asset current_location_id not updated',
                ];
            }

            // Cleanup
            $movement->delete();
            $asset->delete();
            $location1->delete();
            $location2->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Asset movement tracking failed. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_multiple_movements_create_complete_history()
    {
        $iterations = 50;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create 3 locations
            $locations = [];
            for ($j = 0; $j < 3; $j++) {
                $locations[] = MdmOrganizationUnit::create([
                    'code' => 'LOC' . $i . chr(65 + $j),
                    'name' => 'Location ' . chr(65 + $j) . ' ' . $i,
                    'type' => 'unit',
                    'level' => 1,
                    'is_active' => true,
                ]);
            }

            // Create asset at first location
            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 1000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => rand(1000000, 10000000),
                'acquisition_date' => Carbon::now()->subMonths(rand(1, 60)),
                'current_location_id' => $locations[0]->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            // Move asset through all locations
            $expectedMovements = 2; // From loc0 to loc1, then loc1 to loc2
            for ($j = 0; $j < $expectedMovements; $j++) {
                $fromLocation = $locations[$j];
                $toLocation = $locations[$j + 1];

                MdmAssetMovement::create([
                    'asset_id' => $asset->id,
                    'from_location_id' => $fromLocation->id,
                    'to_location_id' => $toLocation->id,
                    'movement_date' => Carbon::now()->subDays(($expectedMovements - $j) * 10),
                    'reason' => 'Movement ' . ($j + 1),
                    'created_by' => 1,
                ]);

                $asset->update(['current_location_id' => $toLocation->id]);
            }

            // Verify movement count
            $actualMovements = $asset->movements()->count();
            if ($actualMovements !== $expectedMovements) {
                $failures[] = [
                    'iteration' => $i,
                    'expected_movements' => $expectedMovements,
                    'actual_movements' => $actualMovements,
                    'reason' => 'Movement count mismatch',
                ];
            }

            // Verify final location
            $asset->refresh();
            if ($asset->current_location_id !== $locations[2]->id) {
                $failures[] = [
                    'iteration' => $i,
                    'expected_final_location' => $locations[2]->id,
                    'actual_final_location' => $asset->current_location_id,
                    'reason' => 'Final location mismatch',
                ];
            }

            // Cleanup
            $asset->movements()->delete();
            $asset->delete();
            foreach ($locations as $location) {
                $location->delete();
            }
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Multiple movements did not create complete history. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_movement_preserves_chronological_order()
    {
        $iterations = 50;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Create locations
            $location1 = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 2000) . 'A',
                'name' => 'Location A ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $location2 = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 2000) . 'B',
                'name' => 'Location B ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            $location3 = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 2000) . 'C',
                'name' => 'Location C ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create asset
            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 2000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => rand(1000000, 10000000),
                'acquisition_date' => Carbon::now()->subMonths(rand(1, 60)),
                'current_location_id' => $location1->id,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            // Create movements with specific dates
            $date1 = Carbon::now()->subDays(30);
            $date2 = Carbon::now()->subDays(15);

            MdmAssetMovement::create([
                'asset_id' => $asset->id,
                'from_location_id' => $location1->id,
                'to_location_id' => $location2->id,
                'movement_date' => $date1,
                'reason' => 'First movement',
                'created_by' => 1,
            ]);

            MdmAssetMovement::create([
                'asset_id' => $asset->id,
                'from_location_id' => $location2->id,
                'to_location_id' => $location3->id,
                'movement_date' => $date2,
                'reason' => 'Second movement',
                'created_by' => 1,
            ]);

            // Get movements ordered by date
            $movements = $asset->movements()->orderBy('movement_date')->get();

            // Verify chronological order
            if ($movements->count() === 2) {
                $firstMovement = $movements[0];
                $secondMovement = $movements[1];

                if ($firstMovement->movement_date->gt($secondMovement->movement_date)) {
                    $failures[] = [
                        'iteration' => $i,
                        'first_date' => $firstMovement->movement_date->toDateString(),
                        'second_date' => $secondMovement->movement_date->toDateString(),
                        'reason' => 'Movements not in chronological order',
                    ];
                }

                // Verify movement chain
                if ($firstMovement->to_location_id !== $secondMovement->from_location_id) {
                    $failures[] = [
                        'iteration' => $i,
                        'first_to' => $firstMovement->to_location_id,
                        'second_from' => $secondMovement->from_location_id,
                        'reason' => 'Movement chain broken',
                    ];
                }
            }

            // Cleanup
            $asset->movements()->delete();
            $asset->delete();
            $location1->delete();
            $location2->delete();
            $location3->delete();
        }

        $this->assertEmpty(
            $failures,
            'Property violated: Movement chronological order not preserved. Failures: ' . json_encode($failures)
        );
    }

    /**
     * @test
     */
    public function property_movement_from_null_location_is_allowed()
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            // Create location
            $location = MdmOrganizationUnit::create([
                'code' => 'LOC' . ($i + 3000),
                'name' => 'Location ' . $i,
                'type' => 'unit',
                'level' => 1,
                'is_active' => true,
            ]);

            // Create asset without initial location
            $asset = MdmAsset::create([
                'code' => 'ASSET' . str_pad($i + 3000, 6, '0', STR_PAD_LEFT),
                'name' => 'Asset ' . $i,
                'category' => $this->randomCategory(),
                'acquisition_value' => rand(1000000, 10000000),
                'acquisition_date' => Carbon::now()->subMonths(rand(1, 60)),
                'current_location_id' => null,
                'condition' => 'baik',
                'is_active' => true,
            ]);

            // Create movement from null location
            $movement = MdmAssetMovement::create([
                'asset_id' => $asset->id,
                'from_location_id' => null,
                'to_location_id' => $location->id,
                'movement_date' => Carbon::now(),
                'reason' => 'Initial placement',
                'created_by' => 1,
            ]);

            $asset->update(['current_location_id' => $location->id]);

            // Verify movement was created
            $this->assertNotNull($movement->id);
            $this->assertNull($movement->from_location_id);
            $this->assertEquals($location->id, $movement->to_location_id);

            // Verify asset location updated
            $asset->refresh();
            $this->assertEquals($location->id, $asset->current_location_id);

            // Cleanup
            $movement->delete();
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
