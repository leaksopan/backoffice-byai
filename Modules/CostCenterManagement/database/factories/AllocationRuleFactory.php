<?php

namespace Modules\CostCenterManagement\Database\Factories;

use Modules\CostCenterManagement\Models\AllocationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class AllocationRuleFactory extends Factory
{
    protected $model = AllocationRule::class;

    public function definition(): array
    {
        return [
            'code' => 'AR-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => 'Allocation Rule ' . $this->faker->words(3, true),
            'source_cost_center_id' => 1, // Will be overridden
            'allocation_base' => $this->faker->randomElement(['percentage', 'direct', 'formula']),
            'allocation_formula' => null,
            'is_active' => true,
            'effective_date' => now(),
            'end_date' => null,
            'approval_status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'justification' => $this->faker->sentence,
            'created_by' => 1,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
        ]);
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'allocation_base' => 'percentage',
        ]);
    }

    public function formula(): static
    {
        return $this->state(fn (array $attributes) => [
            'allocation_base' => 'formula',
            'allocation_formula' => 'source_amount * 0.5',
        ]);
    }
}
