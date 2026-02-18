# Implementation Plan: Master Data Management (M02)

## Overview

Implementasi modul Master Data Management akan dilakukan secara incremental, dimulai dari setup struktur modul, kemudian implementasi setiap kategori data master (Organization, COA, Funding Source, Service, Tariff, HR, Asset), dan diakhiri dengan integrasi API dan testing. Setiap task akan menghasilkan working code yang terintegrasi dengan task sebelumnya.

## Tasks

- [x] 1. Setup module structure dan core configuration
  - Generate module menggunakan nwidart/laravel-modules
  - Setup service provider, routes, dan middleware
  - Create base controller dan model classes
  - Setup permissions dan seeder
  - _Requirements: 10.1_

- [x] 2. Implement Organization Unit Management
  - [x] 2.1 Create migration dan model untuk mdm_organization_units
    - Migration dengan fields: code, name, type, parent_id, hierarchy_path, level, is_active
    - Model dengan relationships (parent, children)
    - _Requirements: 1.1_

  - [x] 2.2 Implement OrganizationHierarchyService
    - Method validateNoCircularReference()
    - Method updateHierarchyPath()
    - Method getDescendants()
    - Method canDelete()
    - _Requirements: 1.2, 1.4_

  - [x] 2.3 Create OrganizationUnitController dengan CRUD operations
    - index(), create(), store(), edit(), update(), destroy()
    - tree() method untuk visualisasi hierarki
    - Form validation requests
    - _Requirements: 1.1, 1.3, 1.6_

  - [x] 2.4 Write property test for circular reference prevention
    - **Property 1: Circular Reference Prevention**
    - **Validates: Requirements 1.2**

  - [x] 2.5 Write property test for hierarchy path consistency
    - **Property 2: Hierarchy Path Consistency**
    - **Validates: Requirements 1.4**

  - [x] 2.6 Write property test for referential integrity (organization units)
    - **Property 3: Referential Integrity Protection**
    - **Validates: Requirements 1.3**

  - [x] 2.7 Create Filament resource untuk Organization Unit management
    - Form dengan parent selector (tree dropdown)
    - Table dengan hierarchy visualization
    - _Requirements: 1.1_

- [x] 3. Implement Chart of Accounts Management
  - [x] 3.1 Create migration dan model untuk mdm_chart_of_accounts
    - Migration dengan fields: code, name, category, normal_balance, parent_id, level, is_header, is_active, external_code
    - Model dengan relationships dan scopes
    - _Requirements: 2.1, 2.2_

  - [x] 3.2 Implement CoaValidationService
    - Method validateCoaFormat() dengan regex X-XX-XX-XX-XXX
    - Method canPostTransaction() check is_header
    - Method canDelete() check usage
    - Method parseCoaStructure()
    - _Requirements: 2.2, 2.3, 2.6_

  - [x] 3.3 Create ChartOfAccountController dengan CRUD operations
    - index() dengan filter by category dan status
    - store() dengan format validation
    - export() dan import() methods
    - _Requirements: 2.2, 2.6_

  - [x] 3.4 Write property test for COA format validation
    - **Property 5: COA Format Validation**
    - **Validates: Requirements 2.2**

  - [x] 3.5 Write property test for header account posting prevention
    - **Property 6: Header Account Posting Prevention**
    - **Validates: Requirements 2.3**

  - [x] 3.6 Create Filament resource untuk COA management
    - Form dengan parent selector dan category filter
    - Table dengan tree view dan category badges
    - Import/export functionality
    - _Requirements: 2.2_

- [x] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implement Funding Source Management
  - [x] 5.1 Create migration dan model untuk mdm_funding_sources
    - Migration dengan fields: code, name, type, start_date, end_date, is_active
    - Model dengan scopes untuk active sources
    - _Requirements: 3.1, 3.2_

  - [x] 5.2 Create FundingSourceController dengan CRUD operations
    - index(), store(), update(), destroy()
    - checkAvailability() method untuk validasi periode
    - _Requirements: 3.2, 3.4, 3.6_

  - [x] 5.3 Write property test for unique code constraint (funding sources)
    - **Property 7: Unique Code Constraint**
    - **Validates: Requirements 3.3**

  - [x] 5.4 Write property test for period validity check
    - **Property 8: Period Validity Check**
    - **Validates: Requirements 3.4**

  - [x] 5.5 Write property test for inactive entity prevention (funding sources)
    - **Property 4: Inactive Entity Prevention**
    - **Validates: Requirements 3.6**

  - [x] 5.6 Create Filament resource untuk Funding Source management
    - Form dengan date range picker
    - Table dengan type filter dan status badges
    - _Requirements: 3.2_

- [x] 6. Implement Service Catalog Management
  - [x] 6.1 Create migration dan model untuk mdm_service_catalogs
    - Migration dengan fields: code, name, category, unit_id, inacbg_code, standard_duration, is_active
    - Model dengan relationship ke organization_units
    - _Requirements: 4.1, 4.2_

  - [x] 6.2 Create ServiceCatalogController dengan CRUD operations
    - index() dengan filter by category dan unit
    - searchByCode() untuk lookup cepat
    - _Requirements: 4.2, 4.3_

  - [x] 6.3 Write property test for unique code constraint (services)
    - **Property 7: Unique Code Constraint**
    - **Validates: Requirements 4.3**

  - [x] 6.4 Create Filament resource untuk Service Catalog management
    - Form dengan unit selector dan category dropdown
    - Table dengan category filter dan unit badges
    - _Requirements: 4.2_

- [x] 7. Implement Tariff Management
  - [x] 7.1 Create migrations dan models untuk mdm_tariffs dan mdm_tariff_breakdowns
    - Migration mdm_tariffs: service_id, service_class, tariff_amount, start_date, end_date, payer_type, is_active
    - Migration mdm_tariff_breakdowns: tariff_id, component_type, amount, percentage
    - Models dengan relationships
    - _Requirements: 5.1, 5.2_

  - [x] 7.2 Implement TariffCalculationService
    - Method getApplicableTariff() dengan query by service, class, payer, date
    - Method validateNoPeriodOverlap()
    - Method calculateTotalTariff() dari breakdown
    - _Requirements: 5.3, 5.6_

  - [x] 7.3 Create TariffController dengan CRUD operations
    - index() dengan filter by service, class, period
    - getApplicableTariff() API endpoint
    - history() untuk version history
    - _Requirements: 5.1, 5.6_

  - [x] 7.4 Write property test for tariff period overlap prevention
    - **Property 9: Tariff Period Overlap Prevention**
    - **Validates: Requirements 5.3**

  - [x] 7.5 Write property test for applicable tariff retrieval
    - **Property 10: Applicable Tariff Retrieval**
    - **Validates: Requirements 5.6**

  - [x] 7.6 Create Filament resource untuk Tariff management
    - Form dengan service selector, class dropdown, date range, breakdown repeater
    - Table dengan service filter dan period display
    - _Requirements: 5.1_

- [x] 8. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Implement Human Resource Management
  - [x] 9.1 Create migrations dan models untuk mdm_human_resources dan mdm_hr_assignments
    - Migration mdm_human_resources: nip, name, category, position, employment_status, grade, basic_salary, effective_hours_per_week, is_active
    - Migration mdm_hr_assignments: hr_id, unit_id, allocation_percentage, start_date, end_date, is_active
    - Models dengan relationships
    - _Requirements: 6.1, 6.2_

  - [x] 9.2 Create HumanResourceController dengan CRUD operations
    - index() dengan filter by category, unit, status
    - assignments() dan storeAssignment() untuk penugasan
    - Validation untuk total allocation <= 100%
    - _Requirements: 6.2, 6.6, 6.7_

  - [x] 9.3 Write property test for unique code constraint (NIP)
    - **Property 7: Unique Code Constraint**
    - **Validates: Requirements 6.2**

  - [x] 9.4 Write property test for HR allocation percentage limit
    - **Property 11: HR Allocation Percentage Limit**
    - **Validates: Requirements 6.6**

  - [x] 9.5 Write property test for inactive entity prevention (HR)
    - **Property 4: Inactive Entity Prevention**
    - **Validates: Requirements 6.7**

  - [x] 9.6 Create Filament resource untuk HR management
    - Form dengan category dropdown dan unit selector
    - Relation manager untuk assignments dengan allocation percentage
    - _Requirements: 6.2_

- [x] 10. Implement Asset Management
  - [x] 10.1 Create migrations dan models untuk mdm_assets dan mdm_asset_movements
    - Migration mdm_assets: code, name, category, acquisition_value, acquisition_date, useful_life_years, depreciation_method, residual_value, current_location_id, condition, is_active
    - Migration mdm_asset_movements: asset_id, from_location_id, to_location_id, movement_date, reason, approved_by
    - Models dengan relationships
    - _Requirements: 7.1, 7.2_

  - [x] 10.2 Implement AssetDepreciationService
    - Method calculateMonthlyDepreciation() untuk straight_line dan declining_balance
    - Method calculateAccumulatedDepreciation()
    - Method getBookValue()
    - _Requirements: 7.3, 7.4_

  - [x] 10.3 Create AssetController dengan CRUD operations
    - index() dengan filter by category, location, status
    - move() method untuk perpindahan aset
    - depreciationReport() untuk laporan depresiasi
    - _Requirements: 7.2, 7.6_

  - [x] 10.4 Write property test for asset depreciation calculation
    - **Property 12: Asset Depreciation Calculation**
    - **Validates: Requirements 7.3, 7.4**

  - [x] 10.5 Write property test for asset movement tracking
    - **Property 13: Asset Movement Tracking**
    - **Validates: Requirements 7.6**

  - [x] 10.6 Create Filament resource untuk Asset management
    - Form dengan category dropdown, location selector, depreciation fields
    - Relation manager untuk movements
    - Depreciation schedule display
    - _Requirements: 7.2_

- [x] 11. Implement Integration APIs
  - [x] 11.1 Create API routes dan controllers untuk data master
    - Organization units API endpoints
    - COA API endpoints (with postable filter)
    - Funding sources API endpoints (with active-on date filter)
    - Services API endpoints (with category and unit filters)
    - Tariffs API endpoints (with applicable tariff lookup)
    - HR API endpoints (with unit filter)
    - Assets API endpoints (with location filter)
    - _Requirements: 8.1, 8.2, 8.3_

  - [x] 11.2 Implement event dispatching untuk data changes
    - Create MasterDataCreated, MasterDataUpdated, MasterDataDeleted events
    - Dispatch events dari controllers
    - _Requirements: 8.4_

  - [x] 11.3 Write property test for referential integrity protection (general)
    - **Property 3: Referential Integrity Protection**
    - **Validates: Requirements 8.5**

  - [x] 11.4 Write property test for export-import round trip
    - **Property 14: Export-Import Round Trip**
    - **Validates: Requirements 8.7**

- [x] 12. Implement Validation and Security
  - [x] 12.1 Create Form Request classes untuk semua entities
    - Validation rules untuk mandatory fields
    - Custom validation rules untuk format codes
    - _Requirements: 9.1_

  - [x] 12.2 Write property test for mandatory field validation
    - **Property 15: Mandatory Field Validation**
    - **Validates: Requirements 9.1**

  - [x] 12.3 Implement permission checks di controllers
    - Middleware untuk master-data-management permissions
    - Gate checks untuk specific operations
    - _Requirements: 10.1, 10.2_

  - [x] 12.4 Write property test for permission-based access control
    - **Property 16: Permission-Based Access Control**
    - **Validates: Requirements 10.2**

  - [x] 12.5 Write unit test for 403 error on missing permission
    - Test unauthorized access returns 403
    - **Validates: Requirements 10.6**

- [x] 13. Implement Dashboard and Reporting
  - [x] 13.1 Create MdmDashboardController
    - Summary statistics untuk setiap kategori data master
    - Recent changes log
    - Data quality metrics
    - _Requirements: 1.1-10.7_

  - [x] 13.2 Create views untuk dashboard
    - Dashboard layout dengan cards untuk setiap kategori
    - Charts untuk data distribution
    - _Requirements: 1.1-10.7_

- [x] 14. Setup Module Registration
  - [x] 14.1 Create seeder untuk module registration
    - Insert record ke tabel modules dengan moduleKey 'master-data-management'
    - Create menu entries di module_menus
    - _Requirements: 10.1_

  - [x] 14.2 Create seeder untuk permissions
    - Create 5 permissions: access, view, create, edit, delete
    - Assign permissions ke admin role
    - _Requirements: 10.1_

  - [x] 14.3 Create seeder untuk sample data
    - Sample organization units
    - Sample COA (basic structure)
    - Sample funding sources
    - _Requirements: 1.1, 2.1, 3.1_

- [x] 15. Final Integration and Testing
  - [x] 15.1 Run all property-based tests
    - Execute all 16 property tests dengan 100 iterations each
    - Fix any failures
    - _Requirements: All_

  - [x] 15.2 Run integration tests
    - Test API endpoints
    - Test event dispatching
    - Test cross-entity relationships
    - _Requirements: 8.1-8.7_

  - [x] 15.3 Manual testing checklist
    - Test CRUD operations untuk setiap entity
    - Test validation rules
    - Test permission checks
    - Test export/import functionality
    - _Requirements: All_

- [x] 16. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Implementation menggunakan Laravel 12, Filament v3, dan Livewire v3
- Database migrations akan dibuat di root `database/migrations/`
- Module structure mengikuti nwidart/laravel-modules convention
