<?php

namespace Modules\CostCenterManagement\Database\Seeders;

use Illuminate\Database\Seeder;

class CostCenterManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DummyOrganizationUnitsSeeder::class,
            CostCenterManagementModuleSeeder::class,
            CostCenterSampleDataSeeder::class,
        ]);
    }
}
