<?php

namespace Modules\CostCenterManagement\Database\Factories;

use Modules\CostCenterManagement\Models\CostCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

class CostCenterFactory extends Factory
{
    protected $model = CostCenter::class;

    public function definition(): array
    {
        return [
            'code' => 'CC-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->company . ' Cost Center',
            'type' => $this->faker->randomElement(['medical', 'non_medical', 'administrative', 'profit_center']),
            'classification' => $this->faker->randomElement(['Rawat Jalan', 'Rawat Inap', 'Laboratorium', 'Keuangan']),
            'organization_unit_id' => 1, // Placeholder
            'parent_id' => null,
            'hierarchy_path' => null,
            'level' => 0,
            'manager_user_id' => null,
            'is_active' => true,
            'effective_date' => now(),
            'description' => $this->faker->sentence,
            'created_by' => 1,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function medical(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'medical',
            'classification' => $this->faker->randomElement(['Rawat Jalan', 'Rawat Inap', 'IGD', 'Operasi']),
        ]);
    }

    public function administrative(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'administrative',
            'classification' => $this->faker->randomElement(['Keuangan', 'SDM', 'Umum', 'IT']),
        ]);
    }
}
