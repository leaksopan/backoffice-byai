<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\Services\CoaValidationService;

/**
 * Property Test: Header Account Posting Prevention
 * Feature: master-data-management, Property 6: Header Account Posting Prevention
 * Validates: Requirements 2.3
 */
class CoaHeaderAccountPostingTest extends TestCase
{
    use DatabaseMigrations;

    protected CoaValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CoaValidationService();
    }

    /**
     * Property: For any chart of account that has child accounts (is_header=true),
     * the system should reject direct transaction postings to that account
     *
     * @test
     */
    public function property_prevents_posting_to_header_accounts()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runHeaderAccountPostingTest();
        }
    }

    private function runHeaderAccountPostingTest(): void
    {
        // Generate random hierarchy depth (1-4 levels)
        $depth = rand(1, 4);
        
        // Create a chain of accounts: Parent -> Child -> Grandchild
        $accounts = [];
        $previousAccount = null;

        for ($j = 0; $j < $depth; $j++) {
            $isHeader = ($j < $depth - 1); // Last account is not header
            
            $account = MdmChartOfAccount::create([
                'code' => $this->generateValidCoaCode(),
                'name' => 'Account ' . uniqid(),
                'category' => ['asset', 'liability', 'equity', 'revenue', 'expense'][array_rand(['asset', 'liability', 'equity', 'revenue', 'expense'])],
                'normal_balance' => ['debit', 'credit'][array_rand(['debit', 'credit'])],
                'parent_id' => $previousAccount?->id,
                'level' => $j,
                'is_header' => $isHeader,
                'is_active' => true,
            ]);

            $accounts[] = $account;
            $previousAccount = $account;
        }

        // Test 1: Header accounts (with children) should NOT be postable
        for ($k = 0; $k < count($accounts) - 1; $k++) {
            $headerAccount = $accounts[$k];
            $this->assertTrue($headerAccount->is_header, "Account should be marked as header");
            
            $canPost = $this->service->canPostTransaction($headerAccount);
            $this->assertFalse($canPost, "Header account '{$headerAccount->code}' should NOT be postable");
            
            // Also test via model method
            $this->assertFalse($headerAccount->isPostable(), "Header account should return false for isPostable()");
        }

        // Test 2: Leaf accounts (no children) should be postable
        $leafAccount = $accounts[count($accounts) - 1];
        $this->assertFalse($leafAccount->is_header, "Leaf account should NOT be marked as header");
        
        $canPost = $this->service->canPostTransaction($leafAccount);
        $this->assertTrue($canPost, "Leaf account '{$leafAccount->code}' should be postable");
        
        // Also test via model method
        $this->assertTrue($leafAccount->isPostable(), "Leaf account should return true for isPostable()");

        // Cleanup - delete in reverse order (children first)
        for ($i = count($accounts) - 1; $i >= 0; $i--) {
            $accounts[$i]->delete();
        }
    }

    /**
     * Generate valid COA code: X-XX-XX-XX-XXX
     */
    private function generateValidCoaCode(): string
    {
        return sprintf(
            '%d-%02d-%02d-%02d-%03d',
            rand(1, 9),
            rand(0, 99),
            rand(0, 99),
            rand(0, 99),
            rand(0, 999)
        );
    }

    /**
     * Test edge case: Account explicitly marked as non-header should be postable
     *
     * @test
     */
    public function property_non_header_accounts_are_always_postable()
    {
        for ($i = 0; $i < 50; $i++) {
            $account = MdmChartOfAccount::create([
                'code' => $this->generateValidCoaCode(),
                'name' => 'Non-Header Account ' . uniqid(),
                'category' => ['asset', 'liability', 'equity', 'revenue', 'expense'][array_rand(['asset', 'liability', 'equity', 'revenue', 'expense'])],
                'normal_balance' => ['debit', 'credit'][array_rand(['debit', 'credit'])],
                'parent_id' => null,
                'level' => 0,
                'is_header' => false,
                'is_active' => true,
            ]);

            $canPost = $this->service->canPostTransaction($account);
            $this->assertTrue($canPost, "Non-header account should be postable");
            $this->assertTrue($account->isPostable(), "isPostable() should return true for non-header");

            $account->delete();
        }
    }

    /**
     * Test edge case: Account with is_header=true but no actual children
     * (edge case - should still not be postable based on flag)
     *
     * @test
     */
    public function property_header_flag_determines_postability_regardless_of_children()
    {
        for ($i = 0; $i < 30; $i++) {
            // Create account marked as header but with no children
            $account = MdmChartOfAccount::create([
                'code' => $this->generateValidCoaCode(),
                'name' => 'Header Without Children ' . uniqid(),
                'category' => ['asset', 'liability', 'equity', 'revenue', 'expense'][array_rand(['asset', 'liability', 'equity', 'revenue', 'expense'])],
                'normal_balance' => ['debit', 'credit'][array_rand(['debit', 'credit'])],
                'parent_id' => null,
                'level' => 0,
                'is_header' => true,
                'is_active' => true,
            ]);

            // Should not be postable even without children
            $canPost = $this->service->canPostTransaction($account);
            $this->assertFalse($canPost, "Account marked as header should not be postable even without children");
            $this->assertFalse($account->isPostable(), "isPostable() should return false for header flag");

            $account->delete();
        }
    }

    /**
     * Test specific scenarios
     *
     * @test
     */
    public function test_specific_header_posting_scenarios()
    {
        // Scenario 1: 3-level hierarchy
        $level1 = MdmChartOfAccount::create([
            'code' => '1-00-00-00-000',
            'name' => 'Aset',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $level2 = MdmChartOfAccount::create([
            'code' => '1-01-00-00-000',
            'name' => 'Aset Lancar',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $level1->id,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        $level3 = MdmChartOfAccount::create([
            'code' => '1-01-01-00-000',
            'name' => 'Kas',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $level2->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        // Level 1 and 2 should not be postable
        $this->assertFalse($this->service->canPostTransaction($level1));
        $this->assertFalse($this->service->canPostTransaction($level2));
        
        // Level 3 should be postable
        $this->assertTrue($this->service->canPostTransaction($level3));

        // Cleanup
        $level3->delete();
        $level2->delete();
        $level1->delete();
    }

    /**
     * Test that postable scope works correctly
     *
     * @test
     */
    public function test_postable_scope_filters_correctly()
    {
        // Create mix of header and non-header accounts
        $header1 = MdmChartOfAccount::create([
            'code' => '1-00-00-00-000',
            'name' => 'Header 1',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $postable1 = MdmChartOfAccount::create([
            'code' => '1-01-00-00-000',
            'name' => 'Postable 1',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_header' => false,
            'is_active' => true,
        ]);

        $header2 = MdmChartOfAccount::create([
            'code' => '2-00-00-00-000',
            'name' => 'Header 2',
            'category' => 'liability',
            'normal_balance' => 'credit',
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $postable2 = MdmChartOfAccount::create([
            'code' => '2-01-00-00-000',
            'name' => 'Postable 2',
            'category' => 'liability',
            'normal_balance' => 'credit',
            'level' => 0,
            'is_header' => false,
            'is_active' => true,
        ]);

        // Postable scope should only return non-header accounts
        $postableAccounts = MdmChartOfAccount::postable()->get();
        $this->assertCount(2, $postableAccounts);
        $this->assertTrue($postableAccounts->contains($postable1));
        $this->assertTrue($postableAccounts->contains($postable2));
        $this->assertFalse($postableAccounts->contains($header1));
        $this->assertFalse($postableAccounts->contains($header2));

        // Headers scope should only return header accounts
        $headerAccounts = MdmChartOfAccount::headers()->get();
        $this->assertCount(2, $headerAccounts);
        $this->assertTrue($headerAccounts->contains($header1));
        $this->assertTrue($headerAccounts->contains($header2));

        // Cleanup
        $postable2->delete();
        $header2->delete();
        $postable1->delete();
        $header1->delete();
    }
}
