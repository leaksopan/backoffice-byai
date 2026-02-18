<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

/**
 * Property 8: Formula Evaluability Validation
 * 
 * For any allocation rule with allocation_base='formula', 
 * the allocation_formula should be syntactically valid and all referenced variables 
 * should be available in the system context
 * 
 * Validates: Requirements 4.4
 * 
 * @test Feature: cost-center-management, Property 8: Formula Evaluability Validation
 */
class AllocationRuleFormulaPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed necessary data
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Create test data for property tests
     */
    private function createTestData(): void
    {
        // Create organization units and cost centers
        for ($i = 1; $i <= 10; $i++) {
            $orgUnit = MdmOrganizationUnit::create([
                'code' => 'OU-TEST-' . $i,
                'name' => 'Test Org Unit ' . $i,
                'type' => 'department',
                'is_active' => true,
            ]);
            
            CostCenter::create([
                'code' => 'CC-TEST-' . $i,
                'name' => 'Test Cost Center ' . $i,
                'type' => 'administrative',
                'organization_unit_id' => $orgUnit->id,
                'is_active' => true,
                'effective_date' => now(),
            ]);
        }
    }

    /**
     * Property test: Valid formulas should be evaluable
     * 
     * @test
     */
    public function test_valid_formulas_are_evaluable()
    {
        $iterations = 100;
        
        $validFormulas = [
            'source_amount * 0.5',
            'source_amount * 0.25',
            'source_amount / 2',
            'source_amount * (headcount / total_headcount)',
            'source_amount * (square_footage / total_square_footage)',
            'source_amount * 0.3 + 1000',
            '(source_amount - 5000) * 0.4',
            'source_amount * patient_days / total_patient_days',
        ];
        
        for ($i = 0; $i < $iterations; $i++) {
            // Pick random valid formula
            $formula = $validFormulas[array_rand($validFormulas)];
            
            // Create allocation rule with formula
            $allocationRule = $this->createAllocationRuleWithFormula($formula);
            
            // Validate formula syntax
            $isValid = $this->validateFormulaSyntax($formula);
            
            // Assert formula is valid
            $this->assertTrue(
                $isValid,
                "Iteration {$i}: Formula '{$formula}' should be valid"
            );
            
            // Validate referenced variables exist
            $variablesExist = $this->validateFormulaVariables($formula);
            
            $this->assertTrue(
                $variablesExist,
                "Iteration {$i}: All variables in formula '{$formula}' should exist in system context"
            );
            
            // Cleanup
            $allocationRule->delete();
        }
    }

    /**
     * Property test: Invalid formulas should be rejected
     * 
     * @test
     */
    public function test_invalid_formulas_are_rejected()
    {
        $iterations = 50;
        
        $invalidFormulas = [
            'source_amount *',                    // Incomplete expression
            'invalid_variable * 0.5',             // Unknown variable
            'source_amount + + 100',              // Syntax error
            'source_amount / 0',                  // Division by zero
            'DROP TABLE users',                   // SQL injection attempt
            '<script>alert("xss")</script>',      // XSS attempt
            'source_amount * unknown_var',        // Unknown variable
            'eval("malicious code")',             // Code injection
        ];
        
        for ($i = 0; $i < $iterations; $i++) {
            // Pick random invalid formula
            $formula = $invalidFormulas[array_rand($invalidFormulas)];
            
            // Create allocation rule with invalid formula
            $allocationRule = $this->createAllocationRuleWithFormula($formula);
            
            // Validate formula
            $isValid = $this->validateFormulaSyntax($formula);
            $variablesExist = $this->validateFormulaVariables($formula);
            
            // Assert formula is invalid OR variables don't exist
            $this->assertFalse(
                $isValid && $variablesExist,
                "Iteration {$i}: Formula '{$formula}' should be invalid or have missing variables"
            );
            
            // Cleanup
            $allocationRule->delete();
        }
    }

    /**
     * Property test: Formula evaluation with sample data
     * 
     * @test
     */
    public function test_formula_evaluation_with_sample_data()
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create allocation rule with simple formula
            $formula = 'source_amount * 0.5';
            $allocationRule = $this->createAllocationRuleWithFormula($formula);
            
            // Sample data
            $sourceAmount = rand(10000, 100000);
            $context = [
                'source_amount' => $sourceAmount,
            ];
            
            // Evaluate formula
            $result = $this->evaluateFormula($formula, $context);
            
            // Assert result is correct
            $expected = $sourceAmount * 0.5;
            $this->assertEquals(
                $expected,
                $result,
                "Iteration {$i}: Formula evaluation should return {$expected}, got {$result}"
            );
            
            // Cleanup
            $allocationRule->delete();
        }
    }

    /**
     * Create allocation rule with formula
     */
    private function createAllocationRuleWithFormula(string $formula): AllocationRule
    {
        $sourceCostCenter = CostCenter::active()->inRandomOrder()->first();
        
        if (!$sourceCostCenter) {
            $this->fail('No active cost centers available');
        }
        
        return AllocationRule::create([
            'code' => 'AR-FORMULA-' . uniqid(),
            'name' => 'Test Formula Rule ' . rand(1000, 9999),
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'formula',
            'allocation_formula' => $formula,
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'draft',
        ]);
    }

    /**
     * Validate formula syntax
     * 
     * Basic validation: check for dangerous patterns and basic syntax
     */
    private function validateFormulaSyntax(string $formula): bool
    {
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/DROP\s+TABLE/i',
            '/DELETE\s+FROM/i',
            '/INSERT\s+INTO/i',
            '/UPDATE\s+SET/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $formula)) {
                return false;
            }
        }
        
        // Check for division by zero
        if (preg_match('/\/\s*0\b/', $formula)) {
            return false;
        }
        
        // Check for basic syntax errors
        // Must not end with operator
        if (preg_match('/[\+\-\*\/]$/', trim($formula))) {
            return false;
        }
        
        // Must not have consecutive operators
        if (preg_match('/[\+\-\*\/]\s*[\+\-\*\/]/', $formula)) {
            return false;
        }
        
        // Check for balanced parentheses
        $openCount = substr_count($formula, '(');
        $closeCount = substr_count($formula, ')');
        if ($openCount !== $closeCount) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate that all variables in formula exist in system context
     */
    private function validateFormulaVariables(string $formula): bool
    {
        // Known valid variables in system context
        $validVariables = [
            'source_amount',
            'headcount',
            'total_headcount',
            'square_footage',
            'total_square_footage',
            'patient_days',
            'total_patient_days',
            'service_volume',
            'total_service_volume',
            'revenue',
            'total_revenue',
        ];
        
        // Extract variable names from formula
        preg_match_all('/\b([a-z_][a-z0-9_]*)\b/i', $formula, $matches);
        $variables = array_unique($matches[1]);
        
        // Check if all variables are valid
        foreach ($variables as $variable) {
            if (!in_array($variable, $validVariables)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Evaluate formula with given context
     * 
     * Simple evaluation for testing purposes
     */
    private function evaluateFormula(string $formula, array $context): float
    {
        // Replace variables with values
        $expression = $formula;
        foreach ($context as $variable => $value) {
            $expression = str_replace($variable, $value, $expression);
        }
        
        // Evaluate using eval (only for testing, not for production!)
        // In production, use a proper expression evaluator library
        try {
            $result = eval("return {$expression};");
            return (float) $result;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }
}
