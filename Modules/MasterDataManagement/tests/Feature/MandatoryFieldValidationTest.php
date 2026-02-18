<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Http\Requests\StoreOrganizationUnitRequest;
use Modules\MasterDataManagement\Http\Requests\StoreChartOfAccountRequest;
use Modules\MasterDataManagement\Http\Requests\StoreFundingSourceRequest;
use Modules\MasterDataManagement\Http\Requests\StoreServiceCatalogRequest;
use Modules\MasterDataManagement\Http\Requests\StoreTariffRequest;
use Modules\MasterDataManagement\Http\Requests\StoreHumanResourceRequest;
use Modules\MasterDataManagement\Http\Requests\StoreAssetRequest;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Property Test: Mandatory Field Validation
 * Feature: master-data-management, Property 15: Mandatory Field Validation
 * Validates: Requirements 9.1
 */
class MandatoryFieldValidationTest extends TestCase
{
    use DatabaseMigrations;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user with permissions
        $this->user = User::factory()->create();
        Permission::create(['name' => 'master-data-management.create']);
        Permission::create(['name' => 'master-data-management.edit']);
        $this->user->givePermissionTo(['master-data-management.create', 'master-data-management.edit']);
        $this->actingAs($this->user);
    }

    /**
     * Property: For any master data entity creation or update,
     * the system should reject the operation if any mandatory field
     * is null or empty
     *
     * @test
     */
    public function property_rejects_organization_unit_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runOrganizationUnitValidationTest();
        }
    }

    private function runOrganizationUnitValidationTest(): void
    {
        $mandatoryFields = ['code', 'name', 'type'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'code' => 'ORG' . uniqid(),
            'name' => 'Unit ' . uniqid(),
            'type' => ['installation', 'department', 'unit', 'section'][array_rand(['installation', 'department', 'unit', 'section'])],
        ];

        // Remove random mandatory field
        unset($data[$missingField]);

        $request = new StoreOrganizationUnitRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");
    }

    /**
     * @test
     */
    public function property_rejects_chart_of_account_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runChartOfAccountValidationTest();
        }
    }

    private function runChartOfAccountValidationTest(): void
    {
        $mandatoryFields = ['code', 'name', 'category', 'normal_balance', 'level'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'code' => sprintf('%d-%02d-%02d-%02d-%03d', rand(1, 9), rand(1, 99), rand(1, 99), rand(1, 99), rand(1, 999)),
            'name' => 'Account ' . uniqid(),
            'category' => ['asset', 'liability', 'equity', 'revenue', 'expense'][array_rand(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'normal_balance' => ['debit', 'credit'][array_rand(['debit', 'credit'])],
            'level' => rand(0, 5),
        ];

        unset($data[$missingField]);

        $request = new StoreChartOfAccountRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");
    }

    /**
     * @test
     */
    public function property_rejects_funding_source_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runFundingSourceValidationTest();
        }
    }

    private function runFundingSourceValidationTest(): void
    {
        $mandatoryFields = ['code', 'name', 'type', 'start_date'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'code' => 'FS' . uniqid(),
            'name' => 'Funding ' . uniqid(),
            'type' => ['apbn', 'apbd_provinsi', 'apbd_kab_kota', 'pnbp', 'hibah', 'pinjaman', 'lainnya'][array_rand(['apbn', 'apbd_provinsi', 'apbd_kab_kota', 'pnbp', 'hibah', 'pinjaman', 'lainnya'])],
            'start_date' => now()->format('Y-m-d'),
        ];

        unset($data[$missingField]);

        $request = new StoreFundingSourceRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");
    }

    /**
     * @test
     */
    public function property_rejects_service_catalog_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runServiceCatalogValidationTest();
        }
    }

    private function runServiceCatalogValidationTest(): void
    {
        // Create unit first
        $unit = MdmOrganizationUnit::create([
            'code' => 'ORG' . uniqid(),
            'name' => 'Unit ' . uniqid(),
            'type' => 'installation',
            'is_active' => true,
        ]);

        $mandatoryFields = ['code', 'name', 'category', 'unit_id'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'code' => 'SVC' . uniqid(),
            'name' => 'Service ' . uniqid(),
            'category' => ['rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi', 'persalinan', 'administrasi'][array_rand(['rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi', 'persalinan', 'administrasi'])],
            'unit_id' => $unit->id,
        ];

        unset($data[$missingField]);

        $request = new StoreServiceCatalogRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");

        $unit->forceDelete();
    }

    /**
     * @test
     */
    public function property_rejects_tariff_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runTariffValidationTest();
        }
    }

    private function runTariffValidationTest(): void
    {
        // Create dependencies
        $unit = MdmOrganizationUnit::create([
            'code' => 'ORG' . uniqid(),
            'name' => 'Unit ' . uniqid(),
            'type' => 'installation',
            'is_active' => true,
        ]);

        $mandatoryFields = ['service_id', 'service_class', 'tariff_amount', 'start_date'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'service_id' => 1, // Dummy ID
            'service_class' => ['vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum'][array_rand(['vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum'])],
            'tariff_amount' => rand(10000, 1000000),
            'start_date' => now()->format('Y-m-d'),
        ];

        unset($data[$missingField]);

        $request = new StoreTariffRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");

        $unit->forceDelete();
    }

    /**
     * @test
     */
    public function property_rejects_human_resource_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runHumanResourceValidationTest();
        }
    }

    private function runHumanResourceValidationTest(): void
    {
        $mandatoryFields = ['nip', 'name', 'category', 'position', 'employment_status'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'nip' => 'NIP' . uniqid(),
            'name' => 'Employee ' . uniqid(),
            'category' => ['medis_dokter', 'medis_perawat', 'medis_bidan', 'penunjang_medis', 'administrasi', 'umum'][array_rand(['medis_dokter', 'medis_perawat', 'medis_bidan', 'penunjang_medis', 'administrasi', 'umum'])],
            'position' => 'Position ' . uniqid(),
            'employment_status' => ['pns', 'pppk', 'kontrak', 'honorer'][array_rand(['pns', 'pppk', 'kontrak', 'honorer'])],
        ];

        unset($data[$missingField]);

        $request = new StoreHumanResourceRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");
    }

    /**
     * @test
     */
    public function property_rejects_asset_with_missing_mandatory_fields()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runAssetValidationTest();
        }
    }

    private function runAssetValidationTest(): void
    {
        $mandatoryFields = ['code', 'name', 'category', 'acquisition_value', 'acquisition_date', 'condition'];
        $missingField = $mandatoryFields[array_rand($mandatoryFields)];

        $data = [
            'code' => 'AST' . uniqid(),
            'name' => 'Asset ' . uniqid(),
            'category' => ['tanah', 'gedung', 'peralatan_medis', 'peralatan_non_medis', 'kendaraan', 'inventaris'][array_rand(['tanah', 'gedung', 'peralatan_medis', 'peralatan_non_medis', 'kendaraan', 'inventaris'])],
            'acquisition_value' => rand(1000000, 100000000),
            'acquisition_date' => now()->format('Y-m-d'),
            'condition' => ['baik', 'rusak_ringan', 'rusak_berat'][array_rand(['baik', 'rusak_ringan', 'rusak_berat'])],
        ];

        unset($data[$missingField]);

        $request = new StoreAssetRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails(), "Validation should fail when {$missingField} is missing");
        $this->assertTrue($validator->errors()->has($missingField), "Error should be present for {$missingField}");
    }

    /**
     * Test edge case: empty string should be treated as missing
     *
     * @test
     */
    public function property_rejects_empty_string_as_mandatory_field()
    {
        for ($i = 0; $i < 20; $i++) {
            $data = [
                'code' => '', // Empty string
                'name' => 'Unit ' . uniqid(),
                'type' => 'installation',
            ];

            $request = new StoreOrganizationUnitRequest();
            $validator = Validator::make($data, $request->rules());

            $this->assertTrue($validator->fails(), "Validation should fail for empty string");
            $this->assertTrue($validator->errors()->has('code'), "Error should be present for code");
        }
    }

    /**
     * Test edge case: whitespace-only string should be rejected
     *
     * @test
     */
    public function property_rejects_whitespace_only_as_mandatory_field()
    {
        for ($i = 0; $i < 20; $i++) {
            $data = [
                'code' => '   ', // Whitespace only
                'name' => 'Unit ' . uniqid(),
                'type' => 'installation',
            ];

            $request = new StoreOrganizationUnitRequest();
            $validator = Validator::make($data, $request->rules());

            $this->assertTrue($validator->fails(), "Validation should fail for whitespace-only string");
            $this->assertTrue($validator->errors()->has('code'), "Error should be present for code");
        }
    }
}
