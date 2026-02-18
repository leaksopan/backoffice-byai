<?php

namespace Modules\CostCenterManagement\Database\Factories;

use Modules\CostCenterManagement\Models\CostCenterBudget;
use Illuminate\Database\Eloquent\Factories\Factory;

class CostCenterBudgetFactory extends Factory
{
    protected $model = CostCenterBudget::class;

    public function definition(): array
    {
        $budgetAmount = $this->faker->randomFloat(2, 100000, 10000000);
        $actualAmount = $this->faker->randomFloat(2, 0, $budgetAmount * 1.2);
        $varianceAmount = $actualAmount - $budgetAmount;
        $utilizationPercentage = $budgetAmount > 0 ? ($actualAmount / $budgetAmount) * 100 : 0;

        return [
            'cost_center_id' => 1, // Will be overridden
            'fiscal_year' => now()->year,
            'period_month' => now()->month,
            'category' => $this->faker->randomElement(['personnel', 'supplies', 'services', 'depreciation', 'overhead']),
            'budget_amount' => $budgetAmount,
            'actual_amount' => $actualAmount,
            'variance_amount' => $varianceAmount,
            'utilization_percentage' => $utilizationPercentage,
            'revision_number' => 0,
            'revision_justification' => null,
            'created_by' => 1,
        ];
    }

    public function overBudget(): static
    {
        return $this->state(function (array $attributes) {
            $budgetAmount = $attributes['budget_amount'];
            $actualAmount = $budgetAmount * 1.2;
            
            return [
                'actual_amount' => $actualAmount,
                'variance_amount' => $actualAmount - $budgetAmount,
                'utilization_percentage' => 120.0,
            ];
        });
    }

    public function overThreshold(): static
    {
        return $this->state(function (array $attributes) {
            $budgetAmount = $attributes['budget_amount'];
            $actualAmount = $budgetAmount * 0.85;
            
            return [
                'actual_amount' => $actualAmount,
                'variance_amount' => $actualAmount - $budgetAmount,
                'utilization_percentage' => 85.0,
            ];
        });
    }
}
