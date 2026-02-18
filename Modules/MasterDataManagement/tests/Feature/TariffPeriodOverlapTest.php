<?php

namespace Modules\MasterDataManagement\tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\Models\MdmTariff;
use Modules\MasterDataManagement\Services\TariffCalculationService;
use Tests\TestCase;

/**
 * Property 9: Tariff Period Overlap Prevention
 * 
 * For any combination of service_id, service_class, and payer_type, 
 * the system should reject tariff creation or update if the validity period 
 * overlaps with an existing active tariff for the same combination
 * 
 * Validates: Requirements 5.3
 * 
 * @test Feature: master-data-management, Property 9: Tariff Period Overlap Prevention
 */
class TariffPeriodOverlapTest extends TestCase
{
    use RefreshDatabase;

    private TariffCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TariffCalculationService();
    }

    /** @test */
    public function property_tariff_period_overlap_prevention_with_random_data(): void
    {
        $iterations = 100;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            try {
                $this->runSingleIteration($i);
            } catch (\Exception $e) {
                $failures[] = "Iteration {$i}: " . $e->getMessage();
            }
        }

        $this->assertEmpty($failures, "Property violations found:\n" . implode("\n", $failures));
    }

    private function runSingleIteration(int $iteration): void
    {
        // Generate random service
        $unit = MdmOrganizationUnit::create([
            'code' => 'UNIT' . $iteration . rand(1000, 9999),
            'name' => 'Unit Test ' . $iteration,
            'type' => 'unit',
            'level' => 0,
            'is_active' => true,
        ]);

        $service = MdmServiceCatalog::create([
            'code' => 'SVC' . $iteration . rand(1000, 9999),
            'name' => 'Service Test ' . $iteration,
            'category' => 'rawat_jalan',
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // Generate random tariff parameters
        $serviceClass = $this->randomServiceClass();
        $payerType = $this->randomPayerType();

        // Create first tariff with random period
        $startDate1 = Carbon::now()->addDays(rand(0, 30));
        $endDate1 = rand(0, 1) ? $startDate1->copy()->addDays(rand(30, 365)) : null;

        $tariff1 = MdmTariff::create([
            'service_id' => $service->id,
            'service_class' => $serviceClass,
            'tariff_amount' => rand(10000, 1000000),
            'start_date' => $startDate1,
            'end_date' => $endDate1,
            'payer_type' => $payerType,
            'is_active' => true,
        ]);

        // Generate overlapping period
        $overlapType = rand(1, 4);
        [$startDate2, $endDate2] = $this->generateOverlappingPeriod($startDate1, $endDate1, $overlapType);

        // Validate that overlap is detected
        $isValid = $this->service->validateNoPeriodOverlap(
            $service->id,
            $serviceClass,
            $startDate2,
            $endDate2,
            $payerType
        );

        if ($isValid) {
            throw new \Exception(
                "Overlap not detected for iteration {$iteration}:\n" .
                "Existing: {$startDate1->format('Y-m-d')} to " . ($endDate1 ? $endDate1->format('Y-m-d') : 'null') . "\n" .
                "New: {$startDate2->format('Y-m-d')} to " . ($endDate2 ? $endDate2->format('Y-m-d') : 'null') . "\n" .
                "Overlap type: {$overlapType}"
            );
        }

        // Generate non-overlapping period
        if ($endDate1) {
            // If existing has end date: new period starts after existing ends
            $startDate3 = $endDate1->copy()->addDays(rand(1, 30));
            $endDate3 = rand(0, 1) ? $startDate3->copy()->addDays(rand(30, 365)) : null;
        } else {
            // If existing has no end date (unlimited): new period must end before existing starts
            $endDate3 = $startDate1->copy()->subDays(rand(1, 30));
            $startDate3 = $endDate3->copy()->subDays(rand(30, 90));
        }

        // Validate that non-overlap is accepted
        $isValid = $this->service->validateNoPeriodOverlap(
            $service->id,
            $serviceClass,
            $startDate3,
            $endDate3,
            $payerType
        );

        if (!$isValid) {
            throw new \Exception(
                "Non-overlapping period rejected for iteration {$iteration}:\n" .
                "Existing: {$startDate1->format('Y-m-d')} to " . ($endDate1 ? $endDate1->format('Y-m-d') : 'null') . "\n" .
                "New: {$startDate3->format('Y-m-d')} to " . ($endDate3 ? $endDate3->format('Y-m-d') : 'null')
            );
        }

        // Test with different service_class - should not overlap
        $differentClass = $this->randomServiceClass();
        while ($differentClass === $serviceClass) {
            $differentClass = $this->randomServiceClass();
        }

        $isValid = $this->service->validateNoPeriodOverlap(
            $service->id,
            $differentClass,
            $startDate2,
            $endDate2,
            $payerType
        );

        if (!$isValid) {
            throw new \Exception(
                "Different service_class incorrectly detected as overlap for iteration {$iteration}"
            );
        }

        // Test with different payer_type - should not overlap
        $differentPayer = $this->randomPayerType();
        while ($differentPayer === $payerType) {
            $differentPayer = $this->randomPayerType();
        }

        $isValid = $this->service->validateNoPeriodOverlap(
            $service->id,
            $serviceClass,
            $startDate2,
            $endDate2,
            $differentPayer
        );

        if (!$isValid) {
            throw new \Exception(
                "Different payer_type incorrectly detected as overlap for iteration {$iteration}"
            );
        }
    }

    private function generateOverlappingPeriod(Carbon $existingStart, ?Carbon $existingEnd, int $type): array
    {
        switch ($type) {
            case 1: // New period starts during existing period
                $start = $existingStart->copy()->addDays(rand(1, 15));
                $end = rand(0, 1) ? $start->copy()->addDays(rand(30, 365)) : null;
                break;

            case 2: // New period ends during existing period
                if ($existingEnd) {
                    $start = $existingStart->copy()->subDays(rand(10, 30));
                    $end = $existingEnd->copy()->subDays(rand(1, 10));
                } else {
                    $start = $existingStart->copy()->subDays(rand(10, 30));
                    $end = $existingStart->copy()->addDays(rand(1, 30));
                }
                break;

            case 3: // New period completely contains existing period
                $start = $existingStart->copy()->subDays(rand(10, 30));
                $end = $existingEnd ? $existingEnd->copy()->addDays(rand(10, 30)) : $existingStart->copy()->addDays(rand(60, 90));
                break;

            case 4: // New period is completely contained by existing period
                $start = $existingStart->copy()->addDays(rand(5, 10));
                if ($existingEnd) {
                    $end = $existingEnd->copy()->subDays(rand(5, 10));
                } else {
                    $end = $start->copy()->addDays(rand(10, 30));
                }
                break;

            default:
                $start = $existingStart->copy();
                $end = $existingEnd;
        }

        return [$start, $end];
    }

    private function randomServiceClass(): string
    {
        return ['vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum'][rand(0, 4)];
    }

    private function randomPayerType(): ?string
    {
        $types = [null, 'umum', 'bpjs', 'asuransi_x', 'asuransi_y'];
        return $types[rand(0, 4)];
    }
}
