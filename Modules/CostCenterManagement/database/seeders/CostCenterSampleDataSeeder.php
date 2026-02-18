<?php

namespace Modules\CostCenterManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\AllocationRuleTarget;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostPool;
use Modules\CostCenterManagement\Models\CostPoolMember;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\ServiceLineMember;

class CostCenterSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Seeding sample cost centers...');
            $costCenters = $this->seedCostCenters();
            
            $this->command->info('Seeding sample allocation rules...');
            $this->seedAllocationRules($costCenters);
            
            $this->command->info('Seeding sample cost pools...');
            $this->seedCostPools($costCenters);
            
            $this->command->info('Seeding sample service lines...');
            $this->seedServiceLines($costCenters);
            
            $this->command->info('Seeding sample budgets...');
            $this->seedBudgets($costCenters);
            
            $this->command->info('Cost Center sample data seeded successfully!');
        });
    }

    private function seedCostCenters(): array
    {
        $costCenters = [];
        
        // Administrative Cost Centers (Parent)
        $costCenters['admin_finance'] = CostCenter::create([
            'code' => 'CC-ADM-FIN',
            'name' => 'Keuangan',
            'type' => 'administrative',
            'classification' => 'Keuangan',
            'organization_unit_id' => 1, // Assume org unit exists
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Departemen Keuangan dan Akuntansi',
        ]);

        $costCenters['admin_hr'] = CostCenter::create([
            'code' => 'CC-ADM-HR',
            'name' => 'SDM',
            'type' => 'administrative',
            'classification' => 'SDM',
            'organization_unit_id' => 2,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Departemen Sumber Daya Manusia',
        ]);

        $costCenters['admin_it'] = CostCenter::create([
            'code' => 'CC-ADM-IT',
            'name' => 'IT',
            'type' => 'administrative',
            'classification' => 'IT',
            'organization_unit_id' => 3,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Departemen Teknologi Informasi',
        ]);

        $costCenters['admin_general'] = CostCenter::create([
            'code' => 'CC-ADM-GEN',
            'name' => 'Umum',
            'type' => 'administrative',
            'classification' => 'Umum',
            'organization_unit_id' => 4,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Departemen Umum dan Pemeliharaan',
        ]);

        // Medical Cost Centers
        $costCenters['med_rawat_jalan'] = CostCenter::create([
            'code' => 'CC-MED-RJ',
            'name' => 'Rawat Jalan',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => 5,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Rawat Jalan',
        ]);

        $costCenters['med_rawat_inap'] = CostCenter::create([
            'code' => 'CC-MED-RI',
            'name' => 'Rawat Inap',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => 6,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Rawat Inap',
        ]);

        $costCenters['med_igd'] = CostCenter::create([
            'code' => 'CC-MED-IGD',
            'name' => 'IGD',
            'type' => 'medical',
            'classification' => 'IGD',
            'organization_unit_id' => 7,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Gawat Darurat',
        ]);

        $costCenters['med_ok'] = CostCenter::create([
            'code' => 'CC-MED-OK',
            'name' => 'Operasi',
            'type' => 'medical',
            'classification' => 'Operasi',
            'organization_unit_id' => 8,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Bedah Sentral',
        ]);

        // Non-Medical Cost Centers
        $costCenters['nonmed_lab'] = CostCenter::create([
            'code' => 'CC-NM-LAB',
            'name' => 'Laboratorium',
            'type' => 'non_medical',
            'classification' => 'Laboratorium',
            'organization_unit_id' => 9,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Laboratorium',
        ]);

        $costCenters['nonmed_rad'] = CostCenter::create([
            'code' => 'CC-NM-RAD',
            'name' => 'Radiologi',
            'type' => 'non_medical',
            'classification' => 'Radiologi',
            'organization_unit_id' => 10,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Radiologi',
        ]);

        $costCenters['nonmed_farmasi'] = CostCenter::create([
            'code' => 'CC-NM-FAR',
            'name' => 'Farmasi',
            'type' => 'non_medical',
            'classification' => 'Farmasi',
            'organization_unit_id' => 11,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Farmasi',
        ]);

        $costCenters['nonmed_gizi'] = CostCenter::create([
            'code' => 'CC-NM-GIZ',
            'name' => 'Gizi',
            'type' => 'non_medical',
            'classification' => 'Gizi',
            'organization_unit_id' => 12,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subYear(),
            'description' => 'Instalasi Gizi',
        ]);

        return $costCenters;
    }

    private function seedAllocationRules(array $costCenters): void
    {
        // Allocation Rule: Finance to all operational units
        $ruleFinance = AllocationRule::create([
            'code' => 'AR-FIN-001',
            'name' => 'Alokasi Biaya Keuangan',
            'source_cost_center_id' => $costCenters['admin_finance']->id,
            'allocation_base' => 'percentage',
            'allocation_formula' => null,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(6),
            'end_date' => null,
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => Carbon::now()->subMonths(6),
            'justification' => 'Alokasi biaya overhead keuangan berdasarkan proporsi biaya operasional',
        ]);

        // Targets untuk Finance allocation
        $targets = [
            ['target' => $costCenters['med_rawat_jalan'], 'percentage' => 25.00],
            ['target' => $costCenters['med_rawat_inap'], 'percentage' => 30.00],
            ['target' => $costCenters['med_igd'], 'percentage' => 15.00],
            ['target' => $costCenters['med_ok'], 'percentage' => 20.00],
            ['target' => $costCenters['nonmed_lab'], 'percentage' => 5.00],
            ['target' => $costCenters['nonmed_rad'], 'percentage' => 5.00],
        ];

        foreach ($targets as $target) {
            AllocationRuleTarget::create([
                'allocation_rule_id' => $ruleFinance->id,
                'target_cost_center_id' => $target['target']->id,
                'allocation_percentage' => $target['percentage'],
                'allocation_weight' => null,
            ]);
        }

        // Allocation Rule: IT to all units
        $ruleIT = AllocationRule::create([
            'code' => 'AR-IT-001',
            'name' => 'Alokasi Biaya IT',
            'source_cost_center_id' => $costCenters['admin_it']->id,
            'allocation_base' => 'headcount',
            'allocation_formula' => null,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(6),
            'end_date' => null,
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => Carbon::now()->subMonths(6),
            'justification' => 'Alokasi biaya IT berdasarkan jumlah pegawai per unit',
        ]);

        // Allocation Rule: General/Facility to all units
        $ruleGeneral = AllocationRule::create([
            'code' => 'AR-GEN-001',
            'name' => 'Alokasi Biaya Umum',
            'source_cost_center_id' => $costCenters['admin_general']->id,
            'allocation_base' => 'square_footage',
            'allocation_formula' => null,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(6),
            'end_date' => null,
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => Carbon::now()->subMonths(6),
            'justification' => 'Alokasi biaya pemeliharaan gedung berdasarkan luas ruangan',
        ]);
    }

    private function seedCostPools(array $costCenters): void
    {
        // Cost Pool: Utilities
        $poolUtilities = CostPool::create([
            'code' => 'CP-UTL-001',
            'name' => 'Utilities Pool',
            'pool_type' => 'utilities',
            'allocation_base' => 'square_footage',
            'is_active' => true,
            'description' => 'Pool untuk biaya listrik, air, dan gas',
        ]);

        // Add contributors (administrative units)
        CostPoolMember::create([
            'cost_pool_id' => $poolUtilities->id,
            'cost_center_id' => $costCenters['admin_general']->id,
            'is_contributor' => true,
        ]);

        // Add targets (operational units)
        foreach (['med_rawat_jalan', 'med_rawat_inap', 'med_igd', 'med_ok', 'nonmed_lab', 'nonmed_rad'] as $key) {
            CostPoolMember::create([
                'cost_pool_id' => $poolUtilities->id,
                'cost_center_id' => $costCenters[$key]->id,
                'is_contributor' => false,
            ]);
        }

        // Cost Pool: IT Services
        $poolIT = CostPool::create([
            'code' => 'CP-IT-001',
            'name' => 'IT Services Pool',
            'pool_type' => 'it_services',
            'allocation_base' => 'headcount',
            'is_active' => true,
            'description' => 'Pool untuk biaya layanan IT',
        ]);

        CostPoolMember::create([
            'cost_pool_id' => $poolIT->id,
            'cost_center_id' => $costCenters['admin_it']->id,
            'is_contributor' => true,
        ]);
    }

    private function seedServiceLines(array $costCenters): void
    {
        // Service Line: Rawat Jalan
        $slRawatJalan = ServiceLine::create([
            'code' => 'SL-RJ-001',
            'name' => 'Layanan Rawat Jalan',
            'category' => 'rawat_jalan',
            'is_active' => true,
            'description' => 'Lini layanan rawat jalan',
        ]);

        ServiceLineMember::create([
            'service_line_id' => $slRawatJalan->id,
            'cost_center_id' => $costCenters['med_rawat_jalan']->id,
            'allocation_percentage' => 100.00,
        ]);

        // Service Line: Rawat Inap
        $slRawatInap = ServiceLine::create([
            'code' => 'SL-RI-001',
            'name' => 'Layanan Rawat Inap',
            'category' => 'rawat_inap',
            'is_active' => true,
            'description' => 'Lini layanan rawat inap',
        ]);

        ServiceLineMember::create([
            'service_line_id' => $slRawatInap->id,
            'cost_center_id' => $costCenters['med_rawat_inap']->id,
            'allocation_percentage' => 100.00,
        ]);

        // Service Line: Emergency
        $slIGD = ServiceLine::create([
            'code' => 'SL-IGD-001',
            'name' => 'Layanan Gawat Darurat',
            'category' => 'igd',
            'is_active' => true,
            'description' => 'Lini layanan gawat darurat',
        ]);

        ServiceLineMember::create([
            'service_line_id' => $slIGD->id,
            'cost_center_id' => $costCenters['med_igd']->id,
            'allocation_percentage' => 100.00,
        ]);

        // Service Line: Surgical Services (shared dengan penunjang)
        $slOperasi = ServiceLine::create([
            'code' => 'SL-OK-001',
            'name' => 'Layanan Bedah',
            'category' => 'operasi',
            'is_active' => true,
            'description' => 'Lini layanan bedah termasuk penunjang',
        ]);

        ServiceLineMember::create([
            'service_line_id' => $slOperasi->id,
            'cost_center_id' => $costCenters['med_ok']->id,
            'allocation_percentage' => 70.00,
        ]);

        ServiceLineMember::create([
            'service_line_id' => $slOperasi->id,
            'cost_center_id' => $costCenters['nonmed_lab']->id,
            'allocation_percentage' => 15.00,
        ]);

        ServiceLineMember::create([
            'service_line_id' => $slOperasi->id,
            'cost_center_id' => $costCenters['nonmed_rad']->id,
            'allocation_percentage' => 15.00,
        ]);
    }

    private function seedBudgets(array $costCenters): void
    {
        $currentYear = Carbon::now()->year;
        $categories = ['personnel', 'supplies', 'services', 'depreciation', 'overhead'];
        
        // Budget amounts per category (in millions)
        $budgetTemplates = [
            'admin_finance' => ['personnel' => 500, 'supplies' => 50, 'services' => 100, 'depreciation' => 50, 'overhead' => 100],
            'admin_hr' => ['personnel' => 400, 'supplies' => 30, 'services' => 80, 'depreciation' => 30, 'overhead' => 60],
            'admin_it' => ['personnel' => 600, 'supplies' => 200, 'services' => 300, 'depreciation' => 200, 'overhead' => 100],
            'admin_general' => ['personnel' => 300, 'supplies' => 500, 'services' => 400, 'depreciation' => 300, 'overhead' => 200],
            'med_rawat_jalan' => ['personnel' => 2000, 'supplies' => 500, 'services' => 300, 'depreciation' => 200, 'overhead' => 300],
            'med_rawat_inap' => ['personnel' => 3000, 'supplies' => 1000, 'services' => 500, 'depreciation' => 400, 'overhead' => 500],
            'med_igd' => ['personnel' => 1500, 'supplies' => 400, 'services' => 200, 'depreciation' => 150, 'overhead' => 250],
            'med_ok' => ['personnel' => 2500, 'supplies' => 800, 'services' => 400, 'depreciation' => 500, 'overhead' => 400],
            'nonmed_lab' => ['personnel' => 800, 'supplies' => 600, 'services' => 200, 'depreciation' => 300, 'overhead' => 200],
            'nonmed_rad' => ['personnel' => 700, 'supplies' => 500, 'services' => 150, 'depreciation' => 400, 'overhead' => 150],
            'nonmed_farmasi' => ['personnel' => 600, 'supplies' => 2000, 'services' => 100, 'depreciation' => 200, 'overhead' => 150],
            'nonmed_gizi' => ['personnel' => 500, 'supplies' => 800, 'services' => 100, 'depreciation' => 100, 'overhead' => 100],
        ];

        foreach ($budgetTemplates as $key => $template) {
            if (!isset($costCenters[$key])) {
                continue;
            }

            $costCenter = $costCenters[$key];
            
            // Create budget untuk 12 bulan
            for ($month = 1; $month <= 12; $month++) {
                foreach ($categories as $category) {
                    $budgetAmount = $template[$category] * 1000000; // Convert to actual amount
                    
                    CostCenterBudget::create([
                        'cost_center_id' => $costCenter->id,
                        'fiscal_year' => $currentYear,
                        'period_month' => $month,
                        'category' => $category,
                        'budget_amount' => $budgetAmount,
                        'actual_amount' => 0,
                        'variance_amount' => 0,
                        'utilization_percentage' => 0,
                        'revision_number' => 0,
                        'revision_justification' => null,
                    ]);
                }
            }
        }
    }
}