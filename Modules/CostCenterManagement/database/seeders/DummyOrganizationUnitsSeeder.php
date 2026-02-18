<?php

namespace Modules\CostCenterManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DummyOrganizationUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates dummy organization units for Cost Center Management testing
     * when MDM module is not available yet.
     */
    public function run(): void
    {
        // Check if mdm_organization_units table exists
        if (!DB::getSchemaBuilder()->hasTable('mdm_organization_units')) {
            $this->command->warn('Table mdm_organization_units does not exist. Creating temporary table...');
            
            // Create temporary table
            DB::statement('
                CREATE TABLE IF NOT EXISTS mdm_organization_units (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code VARCHAR(20) UNIQUE NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    is_active BOOLEAN DEFAULT 1,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');
        }

        // Check if data already exists
        $count = DB::table('mdm_organization_units')->count();
        if ($count > 0) {
            $this->command->info("Organization units already exist ({$count} records). Skipping...");
            return;
        }

        $this->command->info('Seeding dummy organization units...');

        $units = [
            ['code' => 'ORG-FIN', 'name' => 'Departemen Keuangan', 'type' => 'department'],
            ['code' => 'ORG-HR', 'name' => 'Departemen SDM', 'type' => 'department'],
            ['code' => 'ORG-IT', 'name' => 'Departemen IT', 'type' => 'department'],
            ['code' => 'ORG-GEN', 'name' => 'Departemen Umum', 'type' => 'department'],
            ['code' => 'ORG-RJ', 'name' => 'Instalasi Rawat Jalan', 'type' => 'installation'],
            ['code' => 'ORG-RI', 'name' => 'Instalasi Rawat Inap', 'type' => 'installation'],
            ['code' => 'ORG-IGD', 'name' => 'Instalasi Gawat Darurat', 'type' => 'installation'],
            ['code' => 'ORG-OK', 'name' => 'Instalasi Bedah Sentral', 'type' => 'installation'],
            ['code' => 'ORG-LAB', 'name' => 'Instalasi Laboratorium', 'type' => 'installation'],
            ['code' => 'ORG-RAD', 'name' => 'Instalasi Radiologi', 'type' => 'installation'],
            ['code' => 'ORG-FAR', 'name' => 'Instalasi Farmasi', 'type' => 'installation'],
            ['code' => 'ORG-GIZ', 'name' => 'Instalasi Gizi', 'type' => 'installation'],
        ];

        foreach ($units as $unit) {
            DB::table('mdm_organization_units')->insert([
                'code' => $unit['code'],
                'name' => $unit['name'],
                'type' => $unit['type'],
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Dummy organization units seeded successfully!');
    }
}
