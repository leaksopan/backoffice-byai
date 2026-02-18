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
 * Property 10: Applicable Tariff Retrieval
 * 
 * For any combination of service_id, service_class, payer_type, and transaction_date, 
 * the system should return the unique tariff where transaction_date falls between 
 * start_date and end_date (or end_date is null) and is_active=true
 * 
 * Validates: Requirements 5.6
 * 
 * @test Feature: master-data-management, Property 10: Applicable Tariff Retrieval
 */
class TariffApplicableRetrievalTest extends TestCase
{
    use RefreshDatabase;

    private TariffCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TariffCalculationService();
    }

    /** @test */
    public function property_applicable_tariff_retrieval_with_random_data(): void
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
        // Create service
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

        $serviceClass = $this->randomServiceClass();
        $payerType = $this->randomPayerType();

        // Create multiple tariffs with different periods
        $baseDate = Carbon::now();
        
        // Past tariff (expired)
        $pastTariff = MdmTariff::create([
            'service_id' => $service->id,
            'service_class' => $serviceClass,
            'tariff_amount' => rand(10000, 50000),
            'start_date' => $baseDate->copy()->subDays(90),
            'end_date' => $baseDate->copy()->subDays(31),
            'payer_type' => $payerType,
            'is_active' => true,
        ]);

        // Current tariff (active)
        $currentTariff = MdmTariff::create([
            'service_id' => $service->id,
            'service_class' => $serviceClass,
            'tariff_amount' => rand(50000, 100000),
            'start_date' => $baseDate->copy()->subDays(30),
            'end_date' => $baseDate->copy()->addDays(30),
            'payer_type' => $payerType,
            'is_active' => true,
        ]);

        // Future tariff (not yet active)
        $futureTariff = MdmTariff::create([
            'service_id' => $service->id,
            'service_class' => $serviceClass,
            'tariff_amount' => rand(100000, 150000),
            'start_date' => $baseDate->copy()->addDays(31),
            'end_date' => $baseDate->copy()->addDays(90),
            'payer_type' => $payerType,
            'is_active' => true,
        ]);

        // Test 1: Query with today's date should return current tariff
        $result = $this->service->getApplicableTariff(
            $service->id,
            $serviceClass,
            $baseDate,
            $payerType
        );

        if (!$result || $result->id !== $currentTariff->id) {
            throw new \Exception(
                "Failed to retrieve current tariff for today's date.\n" .
                "Expected: {$currentTariff->id}, Got: " . ($result ? $result->id : 'null')
            );
        }

        // Test 2: Query with past date should return past tariff
        $pastDate = $baseDate->copy()->subDays(60);
        $result = $this->service->getApplicableTariff(
            $service->id,
            $serviceClass,
            $pastDate,
            $payerType
        );

        if (!$result || $result->id !== $pastTariff->id) {
            throw new \Exception(
                "Failed to retrieve past tariff for past date.\n" .
                "Expected: {$pastTariff->id}, Got: " . ($result ? $result->id : 'null')
            );
        }

        // Test 3: Query with future date should return future tariff
        $futureDate = $baseDate->copy()->addDays(60);
        $result = $this->service->getApplicableTariff(
            $service->id,
            $serviceClass,
            $futureDate,
            $payerType
        );

        if (!$result || $result->id !== $futureTariff->id) {
            throw new \Exception(
                "Failed to retrieve future tariff for future date.\n" .
                "Expected: {$futureTariff->id}, Got: " . ($result ? $result->id : 'null')
            );
        }

        // Test 4: Query with date in gap should return null
        $gapDate = $baseDate->copy()->subDays(30)->subHours(1);
        $result = $this->service->getApplicableTariff(
            $service->id,
            $serviceClass,
            $gapDate,
            $payerType
        );

        if ($result !== null) {
            throw new \Exception(
                "Should return null for date in gap between tariffs.\n" .
                "Got tariff: {$result->id}"
            );
        }

        // Test 5: Inactive tariff should not be returned
        $currentTariff->update(['is_active' => false]);
        $result = $this->service->getApplicableTariff(
            $service->id,
            $serviceClass,
            $baseDate,
            $payerType
        );

        if ($result !== null) {
            throw new \Exception(
                "Should return null for inactive tariff.\n" .
                "Got tariff: {$result->id}"
            );
        }

        // Test 6: Different service_class should return null
        $differentClass = $this->randomServiceClass();
        while ($differentClass === $serviceClass) {
            $differentClass = $this->randomServiceClass();
        }

        $result = $this->service->getApplicableTariff(
            $service->id,
            $differentClass,
            $baseDate,
            $payerType
        );

        if ($result !== null) {
            throw new \Exception(
                "Should return null for different service_class.\n" .
                "Got tariff: {$result->id}"
            );
        }

        // Test 7: Tariff with null end_date (unlimited)
        $unlimitedTariff = MdmTariff::create([
            'service_id' => $service->id,
            'service_class' => 'umum',
            'tariff_amount' => rand(50000, 100000),
            'start_date' => $baseDate->copy()->subDays(10),
            'end_date' => null,
            'payer_type' => $payerType,
            'is_active' => true,
        ]);

        $result = $this->service->getApplicableTariff(
            $service->id,
            'umum',
            $baseDate->copy()->addDays(100),
            $payerType
        );

        if (!$result || $result->id !== $unlimitedTariff->id) {
            throw new \Exception(
                "Failed to retrieve unlimited tariff for far future date.\n" .
                "Expected: {$unlimitedTariff->id}, Got: " . ($result ? $result->id : 'null')
            );
        }
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
