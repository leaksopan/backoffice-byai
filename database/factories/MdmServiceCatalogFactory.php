<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterDataManagement\app\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\app\Models\MdmOrganizationUnit;

class MdmServiceCatalogFactory extends Factory
{
    protected $model = MdmServiceCatalog::class;

    public function definition(): array
    {
        return [
            'code' => 'SVC' . fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi', 'persalinan', 'administrasi']),
            'unit_id' => MdmOrganizationUnit::factory(),
            'inacbg_code' => fake()->optional()->regexify('[A-Z]{2}-[0-9]{3}'),
            'standard_duration' => fake()->optional()->numberBetween(15, 240),
            'is_active' => true,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
