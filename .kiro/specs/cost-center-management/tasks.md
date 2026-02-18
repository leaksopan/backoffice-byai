# Implementation Plan: Cost Center & Responsibility Center Management (M03)

## Overview

Implementasi modul Cost Center & Responsibility Center Management menggunakan Laravel 12 dengan arsitektur modular (nwidart/laravel-modules). Modul ini akan dibangun secara incremental dengan fokus pada core functionality terlebih dahulu, kemudian fitur advanced seperti allocation process dan service line costing. Setiap task akan memvalidasi functionality melalui automated tests.

## Tasks

- [x] 1. Setup module structure dan core models
  - Generate module menggunakan nwidart/laravel-modules
  - Setup module configuration (module.json, routes, service provider)
  - Create migrations untuk core tables: cost_centers, allocation_rules, allocation_rule_targets
  - Create Eloquent models dengan relationships
  - Setup permissions: access cost-center-management, cost-center-management.view, cost-center-management.create, cost-center-management.edit, cost-center-management.delete, cost-center-management.allocate
  - _Requirements: 1.1, 1.2, 14.1_

- [x] 1.1 Write unit tests untuk model relationships
  - Test cost center parent-child relationship
  - Test allocation rule targets relationship
  - _Requirements: 1.1, 4.1_

- [x] 2. Implement Cost Center CRUD dengan Filament
  - [x] 2.1 Create CostCenterResource untuk Filament admin panel
    - Form fields: code, name, type, classification, organization_unit_id, parent_id, manager_user_id, is_active, effective_date, description
    - Table columns dengan filters dan search
    - Integration dengan MDM untuk organization unit selection
    - _Requirements: 1.1, 1.2, 1.4, 1.6_

  - [x] 2.2 Write property test untuk organization unit uniqueness
    - **Property 5: Organization Unit Uniqueness Per Active Cost Center**
    - **Validates: Requirements 1.3**

  - [x] 2.3 Write property test untuk medical cost center org unit type validation
    - **Property 6: Medical Cost Center Org Unit Type Validation**
    - **Validates: Requirements 1.5**

  - [x] 2.4 Write property test untuk code uniqueness
    - **Property 12: Code Uniqueness**
    - **Validates: Requirements 15.2**

  - [x] 2.5 Write property test untuk mandatory field validation
    - **Property 11: Mandatory Field Validation**
    - **Validates: Requirements 15.1**

- [x] 3. Implement Cost Center Hierarchy Service
  - [x] 3.1 Create CostCenterHierarchyService
    - Implement validateNoCircularReference() method
    - Implement updateHierarchyPath() method
    - Implement getDescendants() method
    - Implement getAncestors() method
    - Implement canDelete() method
    - _Requirements: 2.2, 2.3, 2.4, 2.5_

  - [x] 3.2 Write property test untuk circular reference prevention
    - **Property 1: Circular Reference Prevention**
    - **Validates: Requirements 2.2**

  - [x] 3.3 Write property test untuk hierarchy path consistency
    - **Property 2: Hierarchy Path Consistency**
    - **Validates: Requirements 2.3**

  - [x] 3.4 Write property test untuk referential integrity protection
    - **Property 3: Referential Integrity Protection**
    - **Validates: Requirements 2.5**

- [x] 4. Implement Cost Center tree visualization dengan Livewire
  - Create Livewire component untuk tree view
  - Implement expand/collapse functionality
  - Show cost center details on hover
  - _Requirements: 2.7_

- [x] 5. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement Allocation Rule Management
  - [x] 6.1 Create migrations untuk allocation_rules dan allocation_rule_targets tables
    - Include approval workflow fields
    - _Requirements: 4.1, 4.2_

  - [x] 6.2 Create AllocationRule dan AllocationRuleTarget models
    - Define relationships
    - Define scopes untuk active rules
    - _Requirements: 4.1_

  - [x] 6.3 Create AllocationRuleResource untuk Filament
    - Form dengan dynamic target inputs
    - Validation untuk allocation base
    - Approval workflow integration
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

  - [x] 6.4 Write property test untuk total allocation percentage validation
    - **Property 7: Total Allocation Percentage Validation**
    - **Validates: Requirements 4.3**

  - [x] 6.5 Write property test untuk formula evaluability validation
    - **Property 8: Formula Evaluability Validation**
    - **Validates: Requirements 4.4**

  - [x] 6.6 Write property test untuk source-target difference validation
    - **Property 13: Source-Target Difference Validation**
    - **Validates: Requirements 15.3**

- [x] 7. Implement Cost Allocation Service
  - [x] 7.1 Create CostAllocationService
    - Implement validateAllocationRule() method
    - Implement calculateAllocationAmount() method
    - Implement executeAllocation() method
    - Implement validateZeroSum() method
    - _Requirements: 4.3, 4.4, 6.1, 6.5_

  - [x] 7.2 Create migrations untuk allocation_journals table
    - Include batch_id, calculation_detail, status fields
    - _Requirements: 4.7, 6.4_

  - [x] 7.3 Create AllocationJournal model
    - Define relationships
    - Define scopes untuk batch queries
    - _Requirements: 4.7_

  - [x] 7.4 Write property test untuk zero-sum allocation validation
    - **Property 9: Zero-Sum Allocation Validation**
    - **Validates: Requirements 6.5**

  - [x] 7.5 Write unit tests untuk allocation calculation
    - Test percentage-based allocation
    - Test formula-based allocation
    - Test edge cases (zero amounts, rounding)
    - _Requirements: 4.3, 4.4_

- [x] 8. Implement Allocation Process Controller
  - Create AllocationProcessController
  - Implement execute() method dengan queue job
  - Implement status() method untuk real-time monitoring
  - Implement review() method untuk preview results
  - Implement post() method untuk posting to GL
  - Implement rollback() method
  - _Requirements: 6.1, 6.2, 6.3, 6.6_

- [x] 9. Checkpoint - Ensure allocation tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 10. Implement Cost Pool Management
  - [x] 10.1 Create migrations untuk cost_pools dan cost_pool_members tables
    - _Requirements: 7.1, 7.2_

  - [x] 10.2 Create CostPool dan CostPoolMember models
    - Define relationships
    - _Requirements: 7.1_

  - [x] 10.3 Create CostPoolResource untuk Filament
    - Form untuk pool definition
    - Member management interface
    - _Requirements: 7.1, 7.2, 7.3_

  - [x] 10.4 Create CostPoolService
    - Implement accumulateCosts() method
    - Implement allocatePool() method
    - Implement validatePoolAllocationRule() method
    - Implement getPoolBalance() method
    - _Requirements: 7.3, 7.4, 7.5_

  - [x] 10.5 Write unit tests untuk cost pool accumulation dan allocation
    - Test cost accumulation dari multiple cost centers
    - Test pool allocation ke targets
    - _Requirements: 7.3, 7.4_

- [x] 11. Implement Service Line Management
  - [x] 11.1 Create migrations untuk service_lines dan service_line_members tables
    - _Requirements: 9.1, 9.2_

  - [x] 11.2 Create ServiceLine dan ServiceLineMember models
    - Define relationships
    - _Requirements: 9.1_

  - [x] 11.3 Create ServiceLineResource untuk Filament
    - Form untuk service line definition
    - Member management dengan allocation percentage
    - _Requirements: 9.1, 9.2, 9.3_

  - [x] 11.4 Implement service line cost analysis
    - Calculate total costs per service line
    - Calculate profit margin (jika revenue tersedia)
    - Comparative analysis antar service lines
    - _Requirements: 9.4, 9.5, 9.6_

  - [x] 11.5 Write unit tests untuk service line costing
    - Test cost aggregation dari multiple cost centers
    - Test shared cost center allocation
    - _Requirements: 9.2, 9.3, 9.4_

- [x] 12. Implement Cost Center Transaction Management
  - [x] 12.1 Create migration untuk cost_center_transactions table
    - _Requirements: 5.1, 5.2_

  - [x] 12.2 Create CostCenterTransaction model
    - Define relationships
    - Define scopes untuk transaction queries
    - _Requirements: 5.1_

  - [x] 12.3 Implement direct cost assignment logic
    - Integration dengan HR assignments untuk gaji
    - Integration dengan asset locations untuk depresiasi
    - Integration dengan purchase orders untuk material
    - _Requirements: 5.2, 5.3, 5.4_

  - [x] 12.4 Write property test untuk inactive cost center prevention
    - **Property 4: Inactive Cost Center Prevention**
    - **Validates: Requirements 1.7, 5.1**

  - [x] 12.5 Write unit tests untuk direct cost assignment
    - Test gaji allocation berdasarkan HR assignment percentage
    - Test depresiasi allocation berdasarkan asset location
    - _Requirements: 5.3, 5.4_

- [x] 13. Implement Budget Management
  - [x] 13.1 Create migration untuk cost_center_budgets table
    - Include revision tracking fields
    - _Requirements: 11.1, 11.2_

  - [x] 13.2 Create CostCenterBudget model
    - Define relationships
    - Define scopes untuk budget queries
    - _Requirements: 11.1_

  - [x] 13.3 Create BudgetTrackingService
    - Implement setBudget() method
    - Implement getAvailableBudget() method
    - Implement updateBudgetUtilization() method
    - Implement checkBudgetThreshold() method
    - Implement calculateVariance() method
    - Implement reviseBudget() method
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

  - [x] 13.4 Create budget management interface di Filament
    - Form untuk budget entry per category
    - Budget revision workflow
    - Budget utilization dashboard
    - _Requirements: 11.1, 11.2, 11.5_

  - [x] 13.5 Write unit tests untuk budget tracking
    - Test budget utilization calculation
    - Test budget threshold checking
    - Test variance calculation
    - _Requirements: 11.3, 11.4, 11.6_

- [x] 14. Checkpoint - Ensure budget tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 15. Implement Variance Analysis Service
  - Create VarianceAnalysisService
  - Implement calculateVariance() method
  - Implement classifyVariance() method (favorable/unfavorable)
  - Implement getTrendAnalysis() method
  - Implement compareServiceLines() method
  - Implement generateVarianceReport() method
  - _Requirements: 8.3, 8.4, 8.6_

- [x] 15.1 Write unit tests untuk variance analysis
  - Test variance calculation
  - Test variance classification
  - Test trend analysis
  - _Requirements: 8.3, 8.4, 8.6_

- [x] 16. Implement Integration dengan MDM
  - [x] 16.1 Create event listeners untuk MDM events
    - UpdateCostCenterOnOrgUnitChange listener
    - ReallocateCostOnHRAssignmentChange listener
    - DeactivateCostCenterOnOrgUnitDeactivation listener
    - _Requirements: 10.1, 10.3, 10.5, 10.6_

  - [x] 16.2 Write property test untuk organization unit existence and active check
    - **Property 10: Organization Unit Existence and Active Check**
    - **Validates: Requirements 10.2**

  - [x] 16.3 Write integration tests untuk MDM event handling
    - Test cost center update ketika org unit berubah
    - Test cost reallocation ketika HR assignment berubah
    - Test cascade deactivation ketika org unit di-nonaktifkan
    - _Requirements: 10.1, 10.3, 10.5_

- [ ] 17. Implement Dashboard dan Reporting
  - [x] 17.1 Create CostCenterDashboardController
    - Real-time cost monitoring per cost center
    - Budget vs actual visualization
    - Variance analysis charts
    - _Requirements: 8.2, 8.5_

  - [x] 17.2 Create Livewire components untuk interactive dashboard
    - Cost distribution pie chart
    - Trend line chart
    - Budget variance bar chart
    - Drill-down functionality
    - _Requirements: 8.2, 8.7, 13.1_

  - [x] 17.3 Implement reporting functionality
    - Cost Center Summary report
    - Cost Allocation Detail report
    - Budget vs Actual report
    - Variance Analysis report
    - Trend Analysis report
    - Export ke Excel, PDF, CSV
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

  - [x] 17.4 Write unit tests untuk report generation
    - Test report data accuracy
    - Test export functionality
    - _Requirements: 13.1, 13.4_

- [x] 18. Implement API Endpoints untuk Integration
  - Create API routes untuk cost center queries
  - Create API routes untuk allocation process
  - Create API routes untuk cost transactions
  - Create API routes untuk budget queries
  - Create API routes untuk service line analysis
  - Implement API authentication dan authorization
  - _Requirements: Integration requirements_

- [x] 18.1 Write API integration tests
  - Test all API endpoints
  - Test authentication dan authorization
  - Test error responses
  - _Requirements: Integration requirements_

- [x] 19. Implement Audit Trail
  - Create audit log untuk cost center changes
  - Create audit log untuk allocation rule changes
  - Create audit log untuk allocation execution
  - Create audit log untuk budget changes
  - Implement audit trail report
  - _Requirements: 12.1, 12.2, 12.3, 12.4_

- [x] 19.1 Write unit tests untuk audit logging
  - Test audit log creation
  - Test audit trail report
  - _Requirements: 12.1, 12.3, 12.4_

- [x] 20. Implement Security dan Access Control
  - Implement row-level security untuk cost center managers
  - Implement approval workflow untuk allocation rules
  - Implement approval workflow untuk budget revisions
  - Implement permission checks di semua controllers
  - _Requirements: 14.1, 14.2, 14.3, 14.6, 14.7_

- [x] 20.1 Write unit tests untuk security
  - Test permission checks
  - Test row-level security
  - Test approval workflows
  - _Requirements: 14.1, 14.2, 14.3, 14.7_

- [x] 21. Implement Notifications
  - Create SendBudgetWarningNotification listener
  - Create notification untuk allocation completion
  - Create notification untuk approval requests
  - _Requirements: 8.5_

- [x] 22. Create Seeders
  - Seed default cost center types
  - Seed sample cost centers untuk demo
  - Seed sample allocation rules
  - Seed sample budgets
  - _Requirements: Setup requirements_

- [ ] 23. Final Checkpoint - Integration Testing
  - Run full test suite
  - Test end-to-end allocation process
  - Test integration dengan MDM
  - Test dashboard dan reporting
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 24. Documentation dan Deployment
  - Create README.md untuk module
  - Document API endpoints
  - Document configuration options
  - Create deployment guide
  - _Requirements: Deployment requirements_

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Integration tests validate module interactions
- Focus on core functionality first (tasks 1-9), then advanced features (tasks 10-24)
