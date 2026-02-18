<?php

namespace Modules\CostCenterManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Carbon\Carbon;

class CostCenterTreeTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some organization units for testing
        $orgUnits = MdmOrganizationUnit::where('is_active', true)->limit(20)->get();
        
        if ($orgUnits->isEmpty()) {
            $this->command->warn('No active organization units found. Please seed MDM data first.');
            return;
        }

        // Create root cost centers
        $rootMedical = CostCenter::create([
            'code' => 'CC-MED-001',
            'name' => 'Medical Services',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $orgUnits[0]->id,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(6),
            'description' => 'Root cost center for all medical services',
        ]);

        $rootNonMedical = CostCenter::create([
            'code' => 'CC-NMD-001',
            'name' => 'Non-Medical Services',
            'type' => 'non_medical',
            'classification' => 'Laboratorium',
            'organization_unit_id' => $orgUnits[1]->id,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(6),
            'description' => 'Root cost center for all non-medical services',
        ]);

        $rootAdmin = CostCenter::create([
            'code' => 'CC-ADM-001',
            'name' => 'Administrative Services',
            'type' => 'administrative',
            'classification' => 'Keuangan',
            'organization_unit_id' => $orgUnits[2]->id,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(6),
            'description' => 'Root cost center for all administrative services',
        ]);

        // Create children for Medical Services
        $medicalChild1 = CostCenter::create([
            'code' => 'CC-MED-101',
            'name' => 'Rawat Jalan Umum',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $orgUnits[3]->id,
            'parent_id' => $rootMedical->id,
            'hierarchy_path' => $rootMedical->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Outpatient general services',
        ]);

        $medicalChild2 = CostCenter::create([
            'code' => 'CC-MED-102',
            'name' => 'Rawat Inap',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => $orgUnits[4]->id,
            'parent_id' => $rootMedical->id,
            'hierarchy_path' => $rootMedical->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Inpatient services',
        ]);

        $medicalChild3 = CostCenter::create([
            'code' => 'CC-MED-103',
            'name' => 'IGD',
            'type' => 'medical',
            'classification' => 'IGD',
            'organization_unit_id' => $orgUnits[5]->id,
            'parent_id' => $rootMedical->id,
            'hierarchy_path' => $rootMedical->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Emergency department',
        ]);

        // Create grandchildren for Rawat Inap
        CostCenter::create([
            'code' => 'CC-MED-201',
            'name' => 'Rawat Inap Kelas 1',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => $orgUnits[6]->id,
            'parent_id' => $medicalChild2->id,
            'hierarchy_path' => $rootMedical->id . '/' . $medicalChild2->id,
            'level' => 2,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(4),
            'description' => 'Class 1 inpatient ward',
        ]);

        CostCenter::create([
            'code' => 'CC-MED-202',
            'name' => 'Rawat Inap Kelas 2',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => $orgUnits[7]->id,
            'parent_id' => $medicalChild2->id,
            'hierarchy_path' => $rootMedical->id . '/' . $medicalChild2->id,
            'level' => 2,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(4),
            'description' => 'Class 2 inpatient ward',
        ]);

        CostCenter::create([
            'code' => 'CC-MED-203',
            'name' => 'Rawat Inap VIP',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => $orgUnits[8]->id,
            'parent_id' => $medicalChild2->id,
            'hierarchy_path' => $rootMedical->id . '/' . $medicalChild2->id,
            'level' => 2,
            'is_active' => false, // Inactive for testing
            'effective_date' => Carbon::now()->subMonths(4),
            'description' => 'VIP inpatient ward (currently inactive)',
        ]);

        // Create children for Non-Medical Services
        CostCenter::create([
            'code' => 'CC-NMD-101',
            'name' => 'Laboratorium Klinik',
            'type' => 'non_medical',
            'classification' => 'Laboratorium',
            'organization_unit_id' => $orgUnits[9]->id,
            'parent_id' => $rootNonMedical->id,
            'hierarchy_path' => $rootNonMedical->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Clinical laboratory services',
        ]);

        CostCenter::create([
            'code' => 'CC-NMD-102',
            'name' => 'Radiologi',
            'type' => 'non_medical',
            'classification' => 'Radiologi',
            'organization_unit_id' => $orgUnits[10]->id,
            'parent_id' => $rootNonMedical->id,
            'hierarchy_path' => $rootNonMedical->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Radiology services',
        ]);

        CostCenter::create([
            'code' => 'CC-NMD-103',
            'name' => 'Farmasi',
            'type' => 'non_medical',
            'classification' => 'Farmasi',
            'organization_unit_id' => $orgUnits[11]->id,
            'parent_id' => $rootNonMedical->id,
            'hierarchy_path' => $rootNonMedical->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Pharmacy services',
        ]);

        // Create children for Administrative Services
        CostCenter::create([
            'code' => 'CC-ADM-101',
            'name' => 'Keuangan',
            'type' => 'administrative',
            'classification' => 'Keuangan',
            'organization_unit_id' => $orgUnits[12]->id,
            'parent_id' => $rootAdmin->id,
            'hierarchy_path' => $rootAdmin->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Finance department',
        ]);

        CostCenter::create([
            'code' => 'CC-ADM-102',
            'name' => 'SDM',
            'type' => 'administrative',
            'classification' => 'SDM',
            'organization_unit_id' => $orgUnits[13]->id,
            'parent_id' => $rootAdmin->id,
            'hierarchy_path' => $rootAdmin->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Human resources department',
        ]);

        CostCenter::create([
            'code' => 'CC-ADM-103',
            'name' => 'IT',
            'type' => 'administrative',
            'classification' => 'IT',
            'organization_unit_id' => $orgUnits[14]->id,
            'parent_id' => $rootAdmin->id,
            'hierarchy_path' => $rootAdmin->id,
            'level' => 1,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(5),
            'description' => 'Information technology department',
        ]);

        // Create a profit center
        CostCenter::create([
            'code' => 'CC-PFT-001',
            'name' => 'Medical Check-Up Center',
            'type' => 'profit_center',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $orgUnits[15]->id,
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'is_active' => true,
            'effective_date' => Carbon::now()->subMonths(3),
            'description' => 'Profit center for medical check-up services',
        ]);

        $this->command->info('Cost center tree test data seeded successfully!');
    }
}
