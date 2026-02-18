# Design Document - Cost Center & Responsibility Center Management (M03)

## Overview

Modul Cost Center & Responsibility Center Management adalah sistem pengelolaan pusat biaya dan pusat pertanggungjawaban yang mengimplementasikan prinsip Activity Based Costing (ABC) untuk BLUD. Modul ini dibangun menggunakan Laravel 12 dengan arsitektur modular (nwidart/laravel-modules), Filament v3 untuk admin panel, dan Livewire v3 untuk dashboard interaktif. Modul ini terintegrasi erat dengan Master Data Management (M02) untuk struktur organisasi dan menjadi fondasi untuk Resource Cost Management (M04) dan Unit Cost Engine (M07).

## Architecture

### Module Structure
```
Modules/CostCenterManagement/
├── app/
│   ├── Http/Controllers/
│   │   ├── CostCenterController.php
│   │   ├── AllocationRuleController.php
│   │   ├── CostPoolController.php
│   │   ├── ServiceLineController.php
│   │   ├── AllocationProcessController.php
│   │   └── CostCenterDashboardController.php
│   ├── Models/
│   │   ├── CostCenter.php
│   │   ├── AllocationRule.php
│   │   ├── AllocationJournal.php
│   │   ├── CostPool.php
│   │   ├── CostPoolMember.php
│   │   ├── ServiceLine.php
│   │   ├── ServiceLineMember.php
│   │   ├── CostCenterBudget.php
│   │   └── CostCenterTransaction.php
│   ├── Services/
│   │   ├── CostCenterHierarchyService.php
│   │   ├── CostAllocationService.php
│   │   ├── CostPoolService.php
│   │   ├── BudgetTrackingService.php
│   │   └── VarianceAnalysisService.php
│   ├── Events/
│   │   ├── CostCenterCreated.php
│   │   ├── CostCenterUpdated.php
│   │   ├── AllocationCompleted.php
│   │   └── BudgetThresholdExceeded.php
│   ├── Listeners/
│   │   ├── UpdateCostCenterOnOrgUnitChange.php
│   │   ├── ReallocateCostOnHRAssignmentChange.php
│   │   └── SendBudgetWarningNotification.php
│   └── Providers/
│       └── CostCenterManagementServiceProvider.php
├── database/migrations/
├── resources/views/
├── Routes/web.php
└── tests/
```

### Technology Stack
- Framework: Laravel 12
- Admin Panel: Filament v3 (untuk CRUD cost center, allocation rules)
- Interactive UI: Livewire v3 (untuk dashboard, allocation process monitoring)
- Database: SQLite (development), PostgreSQL/MySQL (production)
- Validation: Laravel Form Requests
- Authorization: Spatie Laravel Permission v6
- Events: Laravel Event System untuk integrasi dengan MDM

### Module Configuration
- Module Key: `cost-center-management`
- Route Prefix: `ccm`
- Permission Prefix: `cost-center-management`

## Components and Interfaces

### 1. Cost Center Management

**Controller:** `CostCenterController`
**Model:** `CostCenter`

**Methods:**
```php
class CostCenterController extends Controller
{
    public function index(Request $request): View // filter by type, status, org unit
    public function create(): View
    public function store(StoreCostCenterRequest $request): RedirectResponse
    public function edit(CostCenter $costCenter): View
    public function update(UpdateCostCenterRequest $request, CostCenter $costCenter): RedirectResponse
    public function destroy(CostCenter $costCenter): RedirectResponse
    public function tree(): JsonResponse // visualisasi hierarki
    public function activate(CostCenter $costCenter): RedirectResponse
    public function deactivate(CostCenter $costCenter): RedirectResponse
}
```

**Service:** `CostCenterHierarchyService`
```php
class CostCenterHierarchyService
{
    public function validateNoCircularReference(int $costCenterId, ?int $parentId): bool
    public function updateHierarchyPath(CostCenter $costCenter): void
    public function getDescendants(int $costCenterId): Collection
    public function getAncestors(int $costCenterId): Collection
    public function canDelete(CostCenter $costCenter): bool
    public function consolidateCosts(int $costCenterId, Carbon $startDate, Carbon $endDate): float
}
```

### 2. Allocation Rule Management

**Controller:** `AllocationRuleController`
**Model:** `AllocationRule`

**Methods:**
```php
class AllocationRuleController extends Controller
{
    public function index(Request $request): View // filter by source, target, status
    public function create(): View
    public function store(StoreAllocationRuleRequest $request): RedirectResponse
    public function edit(AllocationRule $rule): View
    public function update(UpdateAllocationRuleRequest $request, AllocationRule $rule): RedirectResponse
    public function destroy(AllocationRule $rule): RedirectResponse
    public function simulate(SimulateAllocationRequest $request): JsonResponse
    public function approve(AllocationRule $rule): RedirectResponse
}
```

**Service:** `CostAllocationService`
```php
class CostAllocationService
{
    public function validateAllocationRule(AllocationRule $rule): bool
    public function calculateAllocationAmount(AllocationRule $rule, float $sourceCost): array
    public function executeAllocation(Carbon $periodStart, Carbon $periodEnd): void
    public function executeStepDownAllocation(Carbon $periodStart, Carbon $periodEnd): void
    public function validateZeroSum(Collection $journals): bool
    public function rollbackAllocation(int $allocationBatchId): void
    public function getAllocationSequence(): Collection // untuk step-down method
}
```

### 3. Cost Pool Management

**Controller:** `CostPoolController`
**Model:** `CostPool`, `CostPoolMember`

**Methods:**
```php
class CostPoolController extends Controller
{
    public function index(): View
    public function create(): View
    public function store(StoreCostPoolRequest $request): RedirectResponse
    public function edit(CostPool $pool): View
    public function update(UpdateCostPoolRequest $request, CostPool $pool): RedirectResponse
    public function destroy(CostPool $pool): RedirectResponse
    public function balance(CostPool $pool, Request $request): JsonResponse
    public function allocate(CostPool $pool, AllocateCostPoolRequest $request): RedirectResponse
}
```

**Service:** `CostPoolService`
```php
class CostPoolService
{
    public function accumulateCosts(CostPool $pool, Carbon $periodStart, Carbon $periodEnd): float
    public function allocatePool(CostPool $pool, Carbon $periodStart, Carbon $periodEnd): void
    public function validatePoolAllocationRule(CostPool $pool): bool
    public function getPoolBalance(CostPool $pool, Carbon $asOfDate): float
}
```

### 4. Service Line Management

**Controller:** `ServiceLineController`
**Model:** `ServiceLine`, `ServiceLineMember`

**Methods:**
```php
class ServiceLineController extends Controller
{
    public function index(): View
    public function create(): View
    public function store(StoreServiceLineRequest $request): RedirectResponse
    public function edit(ServiceLine $serviceLine): View
    public function update(UpdateServiceLineRequest $request, ServiceLine $serviceLine): RedirectResponse
    public function destroy(ServiceLine $serviceLine): RedirectResponse
    public function costAnalysis(ServiceLine $serviceLine, Request $request): View
    public function profitabilityReport(ServiceLine $serviceLine, Request $request): View
}
```

### 5. Allocation Process Management

**Controller:** `AllocationProcessController`

**Methods:**
```php
class AllocationProcessController extends Controller
{
    public function index(): View // list allocation batches
    public function create(): View // setup new allocation run
    public function execute(ExecuteAllocationRequest $request): RedirectResponse
    public function status(int $batchId): JsonResponse // real-time status
    public function review(int $batchId): View // review before posting
    public function post(int $batchId): RedirectResponse // post to GL
    public function rollback(int $batchId): RedirectResponse
}
```

### 6. Budget Tracking

**Model:** `CostCenterBudget`

**Service:** `BudgetTrackingService`
```php
class BudgetTrackingService
{
    public function setBudget(int $costCenterId, int $year, int $month, array $categoryAmounts): CostCenterBudget
    public function getAvailableBudget(int $costCenterId, int $year, int $month, string $category): float
    public function updateBudgetUtilization(int $costCenterId, int $year, int $month): void
    public function checkBudgetThreshold(int $costCenterId, int $year, int $month): bool
    public function calculateVariance(int $costCenterId, int $year, int $month): array
    public function reviseBudget(int $budgetId, array $newAmounts, string $justification): CostCenterBudget
}
```

### 7. Variance Analysis

**Service:** `VarianceAnalysisService`
```php
class VarianceAnalysisService
{
    public function calculateVariance(int $costCenterId, Carbon $periodStart, Carbon $periodEnd): array
    public function classifyVariance(float $variance, float $budget): string // 'favorable' or 'unfavorable'
    public function getTrendAnalysis(int $costCenterId, int $months): array
    public function compareServiceLines(array $serviceLineIds, Carbon $periodStart, Carbon $periodEnd): array
    public function generateVarianceReport(array $costCenterIds, Carbon $periodStart, Carbon $periodEnd): Collection
}
```

## Data Models

### CostCenter
```sql
CREATE TABLE cost_centers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('medical', 'non_medical', 'administrative', 'profit_center') NOT NULL,
    classification VARCHAR(100) NULL, -- Rawat Jalan, Laboratorium, Keuangan, dll
    organization_unit_id BIGINT NOT NULL,
    parent_id BIGINT NULL,
    hierarchy_path TEXT NULL,
    level INT NOT NULL DEFAULT 0,
    manager_user_id BIGINT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (organization_unit_id) REFERENCES mdm_organization_units(id) ON DELETE RESTRICT,
    FOREIGN KEY (parent_id) REFERENCES cost_centers(id) ON DELETE RESTRICT,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_org_unit_active (organization_unit_id, is_active),
    INDEX idx_type (type),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active),
    INDEX idx_org_unit (organization_unit_id)
);
```

### AllocationRule
```sql
CREATE TABLE allocation_rules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    source_cost_center_id BIGINT NOT NULL,
    allocation_base ENUM('direct', 'percentage', 'square_footage', 'headcount', 'patient_days', 'service_volume', 'revenue', 'formula') NOT NULL,
    allocation_formula TEXT NULL, -- untuk custom formula
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,
    end_date DATE NULL,
    approval_status ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    justification TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (source_cost_center_id) REFERENCES cost_centers(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_source (source_cost_center_id),
    INDEX idx_active (is_active),
    INDEX idx_status (approval_status)
);
```

### AllocationRuleTarget
```sql
CREATE TABLE allocation_rule_targets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    allocation_rule_id BIGINT NOT NULL,
    target_cost_center_id BIGINT NOT NULL,
    allocation_percentage DECIMAL(5,2) NULL, -- untuk percentage-based
    allocation_weight DECIMAL(10,2) NULL, -- untuk weighted allocation
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (allocation_rule_id) REFERENCES allocation_rules(id) ON DELETE CASCADE,
    FOREIGN KEY (target_cost_center_id) REFERENCES cost_centers(id) ON DELETE RESTRICT,
    INDEX idx_rule (allocation_rule_id),
    INDEX idx_target (target_cost_center_id)
);
```

### AllocationJournal
```sql
CREATE TABLE allocation_journals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id VARCHAR(50) NOT NULL, -- untuk grouping satu run
    allocation_rule_id BIGINT NOT NULL,
    source_cost_center_id BIGINT NOT NULL,
    target_cost_center_id BIGINT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    source_amount DECIMAL(15,2) NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL,
    allocation_base_value DECIMAL(15,2) NULL, -- nilai dasar alokasi
    calculation_detail TEXT NULL, -- JSON detail perhitungan
    status ENUM('draft', 'posted', 'reversed') DEFAULT 'draft',
    posted_at TIMESTAMP NULL,
    posted_by BIGINT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (allocation_rule_id) REFERENCES allocation_rules(id) ON DELETE RESTRICT,
    FOREIGN KEY (source_cost_center_id) REFERENCES cost_centers(id) ON DELETE RESTRICT,
    FOREIGN KEY (target_cost_center_id) REFERENCES cost_centers(id) ON DELETE RESTRICT,
    INDEX idx_batch (batch_id),
    INDEX idx_period (period_start, period_end),
    INDEX idx_status (status),
    INDEX idx_source (source_cost_center_id),
    INDEX idx_target (target_cost_center_id)
);
```

### CostPool
```sql
CREATE TABLE cost_pools (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    pool_type ENUM('utilities', 'facility', 'it_services', 'hr_services', 'finance_services', 'other') NOT NULL,
    allocation_base ENUM('square_footage', 'headcount', 'service_volume', 'revenue', 'equal') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_type (pool_type),
    INDEX idx_active (is_active)
);
```

### CostPoolMember
```sql
CREATE TABLE cost_pool_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cost_pool_id BIGINT NOT NULL,
    cost_center_id BIGINT NOT NULL,
    is_contributor BOOLEAN DEFAULT TRUE, -- TRUE = kontributor, FALSE = target
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (cost_pool_id) REFERENCES cost_pools(id) ON DELETE CASCADE,
    FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pool_member (cost_pool_id, cost_center_id),
    INDEX idx_pool (cost_pool_id),
    INDEX idx_cost_center (cost_center_id)
);
```

### ServiceLine
```sql
CREATE TABLE service_lines (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category ENUM('rawat_jalan', 'rawat_inap', 'igd', 'operasi', 'persalinan', 'icu', 'penunjang') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);
```

### ServiceLineMember
```sql
CREATE TABLE service_line_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_line_id BIGINT NOT NULL,
    cost_center_id BIGINT NOT NULL,
    allocation_percentage DECIMAL(5,2) NOT NULL DEFAULT 100.00, -- untuk shared cost centers
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (service_line_id) REFERENCES service_lines(id) ON DELETE CASCADE,
    FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_service_member (service_line_id, cost_center_id),
    INDEX idx_service_line (service_line_id),
    INDEX idx_cost_center (cost_center_id)
);
```

### CostCenterBudget
```sql
CREATE TABLE cost_center_budgets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cost_center_id BIGINT NOT NULL,
    fiscal_year INT NOT NULL,
    period_month INT NOT NULL, -- 1-12
    category ENUM('personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other') NOT NULL,
    budget_amount DECIMAL(15,2) NOT NULL,
    actual_amount DECIMAL(15,2) DEFAULT 0,
    variance_amount DECIMAL(15,2) DEFAULT 0,
    utilization_percentage DECIMAL(5,2) DEFAULT 0,
    revision_number INT DEFAULT 0,
    revision_justification TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget_period (cost_center_id, fiscal_year, period_month, category, revision_number),
    INDEX idx_cost_center (cost_center_id),
    INDEX idx_period (fiscal_year, period_month)
);
```

### CostCenterTransaction
```sql
CREATE TABLE cost_center_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cost_center_id BIGINT NOT NULL,
    transaction_date DATE NOT NULL,
    transaction_type ENUM('direct_cost', 'allocated_cost', 'revenue') NOT NULL,
    category ENUM('personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reference_type VARCHAR(50) NULL, -- 'salary', 'purchase', 'depreciation', 'allocation'
    reference_id BIGINT NULL,
    description TEXT NULL,
    posted_by BIGINT NULL,
    posted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE RESTRICT,
    INDEX idx_cost_center (cost_center_id),
    INDEX idx_date (transaction_date),
    INDEX idx_type (transaction_type),
    INDEX idx_reference (reference_type, reference_id)
);
```


## Correctness Properties

*Property adalah karakteristik atau perilaku yang harus berlaku untuk semua eksekusi sistem yang valid - pada dasarnya, pernyataan formal tentang apa yang harus dilakukan sistem. Property berfungsi sebagai jembatan antara spesifikasi yang dapat dibaca manusia dan jaminan kebenaran yang dapat diverifikasi mesin.*

### Property Reflection

Setelah menganalisis semua acceptance criteria, berikut adalah properties yang teridentifikasi:

**Testable Properties:**
- 1.3: Uniqueness constraint untuk organization_unit_id + is_active
- 1.5: Medical cost center validation untuk org unit type
- 1.7: Inactive cost center mencegah posting baru
- 2.2: Circular reference prevention
- 2.3: Hierarchy path consistency
- 2.5: Referential integrity protection (cost center dengan children)
- 4.3: Total allocation percentage = 100%
- 4.4: Formula validation
- 5.1: Active cost center validation untuk transaksi
- 6.5: Zero-sum validation untuk alokasi
- 10.2: Organization unit existence dan active check
- 15.1: Mandatory field validation
- 15.2: Code uniqueness
- 15.3: Source != target untuk allocation rule
- 15.4: Total allocation percentage = 100% (REDUNDANT dengan 4.3)
- 15.5: Zero-sum validation (REDUNDANT dengan 6.5)

**Redundancy Analysis:**
1. Properties 15.4 dan 4.3 keduanya tentang "total allocation percentage = 100%" - REDUNDANT, gunakan 4.3
2. Properties 15.5 dan 6.5 keduanya tentang "zero-sum validation" - REDUNDANT, gunakan 6.5
3. Properties 1.7 dan 5.1 keduanya tentang "inactive entity prevention" - bisa digabung menjadi satu property umum
4. Properties 1.3 dan 15.2 keduanya tentang uniqueness - 15.2 lebih spesifik untuk code, 1.3 untuk org_unit+active combo, keduanya perlu
5. Property 2.5 adalah specific case dari referential integrity - keep karena penting untuk hierarchy

**Final Properties (setelah eliminasi redundansi):**
1. Circular reference prevention (2.2)
2. Hierarchy path consistency (2.3)
3. Referential integrity protection (2.5)
4. Inactive cost center prevention (1.7, 5.1 - digabung)
5. Organization unit uniqueness per active cost center (1.3)
6. Medical cost center org unit type validation (1.5)
7. Total allocation percentage validation (4.3)
8. Formula evaluability validation (4.4)
9. Zero-sum allocation validation (6.5)
10. Organization unit existence and active check (10.2)
11. Mandatory field validation (15.1)
12. Code uniqueness (15.2)
13. Source-target difference validation (15.3)

### Correctness Properties

Property 1: Circular Reference Prevention
*For any* cost center and proposed parent cost center, setting the parent relationship should be rejected if it would create a circular reference in the hierarchy (i.e., if the proposed parent is a descendant of the cost center)
**Validates: Requirements 2.2**

Property 2: Hierarchy Path Consistency
*For any* cost center, when its parent is changed, all descendant cost centers should have their hierarchy paths updated to reflect the new structure within the same transaction
**Validates: Requirements 2.3**

Property 3: Referential Integrity Protection
*For any* cost center that has one or more child cost centers, deletion attempts should be rejected with an appropriate error message
**Validates: Requirements 2.5**

Property 4: Inactive Cost Center Prevention
*For any* cost center with is_active=false, the system should reject any attempt to post new transactions or create new allocation rules using that cost center as source or target
**Validates: Requirements 1.7, 5.1**

Property 5: Organization Unit Uniqueness Per Active Cost Center
*For any* organization unit, there should be at most one active cost center (is_active=true) associated with that organization unit at any given time
**Validates: Requirements 1.3**

Property 6: Medical Cost Center Org Unit Type Validation
*For any* cost center with type='medical', the associated organization unit must have type='installation' or type='department'
**Validates: Requirements 1.5**

Property 7: Total Allocation Percentage Validation
*For any* allocation rule with allocation_base='percentage', the sum of allocation_percentage across all target cost centers should equal 100.00
**Validates: Requirements 4.3**

Property 8: Formula Evaluability Validation
*For any* allocation rule with allocation_base='formula', the allocation_formula should be syntactically valid and all referenced variables should be available in the system context
**Validates: Requirements 4.4**

Property 9: Zero-Sum Allocation Validation
*For any* allocation batch execution, the sum of all allocated_amount values in allocation_journals should equal the sum of all source_amount values (i.e., total costs before allocation = total costs after allocation)
**Validates: Requirements 6.5**

Property 10: Organization Unit Existence and Active Check
*For any* cost center, the associated organization_unit_id must reference an existing organization unit in MDM with is_active=true
**Validates: Requirements 10.2**

Property 11: Mandatory Field Validation
*For any* cost center creation or update operation, the fields code, name, type, and organization_unit_id must be non-null and non-empty
**Validates: Requirements 15.1**

Property 12: Code Uniqueness
*For any* two distinct cost centers, their code values must be different (case-insensitive comparison)
**Validates: Requirements 15.2**

Property 13: Source-Target Difference Validation
*For any* allocation rule, the source_cost_center_id must be different from all target_cost_center_id values in the associated allocation_rule_targets
**Validates: Requirements 15.3**

## Error Handling

### Validation Errors
- Circular reference: Return HTTP 422 dengan pesan "Tidak dapat menetapkan parent karena akan membuat circular reference"
- Uniqueness constraint violations: Return HTTP 409 dengan pesan "Kode cost center sudah digunakan" atau "Unit organisasi sudah memiliki cost center aktif"
- Referential integrity violations: Return HTTP 409 dengan pesan "Cost center tidak dapat dihapus karena memiliki child cost centers"
- Inactive cost center usage: Return HTTP 422 dengan pesan "Cost center tidak aktif dan tidak dapat digunakan"
- Medical cost center org unit type: Return HTTP 422 dengan pesan "Cost center medis harus terkait dengan unit organisasi bertipe installation atau department"
- Allocation percentage: Return HTTP 422 dengan pesan "Total persentase alokasi harus 100%, saat ini: {total}%"
- Formula validation: Return HTTP 422 dengan pesan "Formula alokasi tidak valid: {error_detail}"
- Zero-sum validation: Return HTTP 422 dengan pesan "Alokasi gagal validasi zero-sum: selisih {difference}"
- Source-target same: Return HTTP 422 dengan pesan "Source dan target cost center tidak boleh sama"

### Business Logic Errors
- Organization unit not found: Return HTTP 404 dengan pesan "Unit organisasi tidak ditemukan"
- Organization unit inactive: Return HTTP 422 dengan pesan "Unit organisasi tidak aktif"
- Budget threshold exceeded: Return HTTP 422 dengan pesan "Budget utilization melebihi threshold: {percentage}%"
- Allocation rule not approved: Return HTTP 422 dengan pesan "Allocation rule belum diapprove"

### Authorization Errors
- Missing permission: Return HTTP 403 dengan pesan "Anda tidak memiliki akses untuk operasi ini"
- Row-level security: Return HTTP 403 dengan pesan "Anda hanya dapat mengakses cost center yang Anda kelola"

### System Errors
- Database errors: Log error detail, return HTTP 500 dengan pesan generic
- Event processing errors: Log error, retry dengan exponential backoff
- Allocation process errors: Rollback transaction, log error, return HTTP 500

## Testing Strategy

### Unit Tests
Unit tests akan fokus pada:
- Validation logic untuk circular reference detection
- Calculation logic untuk allocation amounts
- Formula parsing dan evaluation
- Zero-sum validation logic
- Hierarchy path update logic
- Permission checking logic
- Specific edge cases seperti:
  - Empty allocation targets
  - Negative amounts
  - Boundary values untuk percentage (0%, 100%)
  - Invalid formula syntax

### Property-Based Tests
Property tests akan menggunakan library PHPUnit dengan extension untuk property-based testing atau Pest PHP. Setiap test akan run minimum 100 iterations.

**Test Configuration:**
- Framework: PHPUnit / Pest PHP
- Minimum iterations: 100 per property test
- Tag format: `@test Feature: cost-center-management, Property {N}: {property_text}`

**Property Test Coverage:**
Properties 1-13 akan diimplementasikan sebagai property-based tests dengan generators untuk:

1. **Random Cost Centers dengan Hierarki:**
```php
function generateCostCenter(): array {
    return [
        'code' => 'CC' . rand(1000, 9999),
        'name' => 'Cost Center ' . Str::random(10),
        'type' => Arr::random(['medical', 'non_medical', 'administrative', 'profit_center']),
        'classification' => Arr::random(['Rawat Jalan', 'Laboratorium', 'Keuangan']),
        'organization_unit_id' => rand(1, 100),
        'is_active' => rand(0, 1) === 1,
        'effective_date' => Carbon::now()->subDays(rand(0, 365)),
    ];
}
```

2. **Random Allocation Rules:**
```php
function generateAllocationRule(int $sourceCostCenterId): array {
    $allocationBase = Arr::random(['percentage', 'square_footage', 'headcount', 'formula']);
    return [
        'code' => 'AR' . rand(1000, 9999),
        'name' => 'Allocation Rule ' . Str::random(10),
        'source_cost_center_id' => $sourceCostCenterId,
        'allocation_base' => $allocationBase,
        'allocation_formula' => $allocationBase === 'formula' ? 'source_amount * 0.5' : null,
        'is_active' => true,
        'effective_date' => Carbon::now(),
    ];
}
```

3. **Random Allocation Targets dengan Percentage:**
```php
function generateAllocationTargets(int $ruleId, int $count): array {
    $targets = [];
    $remainingPercentage = 100.00;
    
    for ($i = 0; $i < $count - 1; $i++) {
        $percentage = round(rand(1, (int)$remainingPercentage - ($count - $i - 1)), 2);
        $targets[] = [
            'allocation_rule_id' => $ruleId,
            'target_cost_center_id' => rand(1, 100),
            'allocation_percentage' => $percentage,
        ];
        $remainingPercentage -= $percentage;
    }
    
    // Last target gets remaining percentage
    $targets[] = [
        'allocation_rule_id' => $ruleId,
        'target_cost_center_id' => rand(1, 100),
        'allocation_percentage' => round($remainingPercentage, 2),
    ];
    
    return $targets;
}
```

4. **Random Allocation Journals untuk Zero-Sum Test:**
```php
function generateAllocationJournals(string $batchId, int $count): array {
    $journals = [];
    $totalSource = 0;
    $totalAllocated = 0;
    
    for ($i = 0; $i < $count; $i++) {
        $sourceAmount = rand(100000, 1000000);
        $allocatedAmount = $sourceAmount; // ensure zero-sum
        
        $journals[] = [
            'batch_id' => $batchId,
            'allocation_rule_id' => rand(1, 50),
            'source_cost_center_id' => rand(1, 100),
            'target_cost_center_id' => rand(1, 100),
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
            'source_amount' => $sourceAmount,
            'allocated_amount' => $allocatedAmount,
            'status' => 'draft',
        ];
        
        $totalSource += $sourceAmount;
        $totalAllocated += $allocatedAmount;
    }
    
    return ['journals' => $journals, 'total_source' => $totalSource, 'total_allocated' => $totalAllocated];
}
```

**Integration Tests:**
- API endpoint testing untuk integrasi dengan MDM
- Event listener testing untuk organization unit changes
- Allocation process end-to-end testing
- Budget tracking integration testing
- Multi-user concurrent access testing

### Test Data Generators

Generators akan menghasilkan data yang:
- Valid dan invalid untuk testing validation rules
- Edge cases seperti circular hierarchies, zero amounts, 100% allocations
- Large datasets untuk performance testing
- Concurrent operations untuk race condition testing

## Integration with Other Modules

### Data Consumers
Modul-modul yang akan menggunakan data dari Cost Center Management:

1. **Resource Cost Management (M04)**: Menggunakan cost centers untuk mengalokasikan biaya SDM, utilitas, dan aset
2. **Activity Dictionary (M05)**: Mengaitkan activities dengan cost centers
3. **Unit Cost Engine (M07)**: Menggunakan cost center costs untuk menghitung unit costs
4. **Service Profitability (M08)**: Menggunakan cost center data untuk analisis profitabilitas
5. **Budgeting System (M10)**: Menggunakan cost centers untuk budget allocation
6. **Accounting & Treasury (M11)**: Posting biaya ke cost centers via journal entries

### Data Providers
Modul-modul yang menyediakan data ke Cost Center Management:

1. **Master Data Management (M02)**: 
   - Organization units untuk cost center mapping
   - HR assignments untuk direct cost allocation
   - Asset locations untuk depreciation allocation
   - Service catalog untuk service line mapping

### API Endpoints for Integration

```php
// Cost Center API
GET /api/ccm/cost-centers
GET /api/ccm/cost-centers/{id}
GET /api/ccm/cost-centers/by-org-unit/{orgUnitId}
GET /api/ccm/cost-centers/by-type/{type}
GET /api/ccm/cost-centers/{id}/descendants
GET /api/ccm/cost-centers/{id}/ancestors
GET /api/ccm/cost-centers/tree

// Cost Allocation API
POST /api/ccm/allocations/execute
GET /api/ccm/allocations/batches
GET /api/ccm/allocations/batches/{batchId}
GET /api/ccm/allocations/batches/{batchId}/journals
POST /api/ccm/allocations/batches/{batchId}/post
POST /api/ccm/allocations/batches/{batchId}/rollback

// Cost Transaction API
POST /api/ccm/transactions
GET /api/ccm/transactions/by-cost-center/{costCenterId}
GET /api/ccm/transactions/summary
  ?cost_center_id={id}&start_date={date}&end_date={date}

// Budget API
GET /api/ccm/budgets/by-cost-center/{costCenterId}
POST /api/ccm/budgets
PUT /api/ccm/budgets/{id}
GET /api/ccm/budgets/{id}/variance

// Service Line API
GET /api/ccm/service-lines
GET /api/ccm/service-lines/{id}
GET /api/ccm/service-lines/{id}/cost-analysis
  ?start_date={date}&end_date={date}
```

### Event Notifications

Cost Center Management akan emit events:

```php
// Events yang akan di-dispatch
CostCenterCreated::class
CostCenterUpdated::class
CostCenterDeactivated::class
AllocationRuleCreated::class
AllocationRuleApproved::class
AllocationCompleted::class
BudgetThresholdExceeded::class

// Event payload example
[
    'cost_center_id' => 123,
    'action' => 'created|updated|deactivated',
    'changed_fields' => ['parent_id', 'is_active'],
    'user_id' => 1,
    'timestamp' => '2026-02-15 10:30:00'
]
```

Cost Center Management akan listen to events dari MDM:

```php
// Events yang akan di-listen
MasterDataUpdated::class // untuk organization unit changes
MasterDataDeactivated::class // untuk cascade deactivation
HRAssignmentChanged::class // untuk reallocate direct costs
AssetLocationChanged::class // untuk reallocate depreciation
```

### Data Synchronization

- **Real-time**: Menggunakan Laravel Events untuk notifikasi perubahan
- **Batch**: Scheduled jobs untuk:
  - Monthly allocation process
  - Budget utilization update
  - Variance calculation
  - Consolidation of child costs to parent
- **Conflict resolution**: Last-write-wins dengan audit trail

## Performance Considerations

### Database Indexing
- Index pada foreign keys untuk join performance
- Index pada frequently queried fields (code, type, is_active, organization_unit_id)
- Composite index untuk cost center lookup (organization_unit_id, is_active)
- Composite index untuk allocation journal lookup (batch_id, status)
- Composite index untuk transaction lookup (cost_center_id, transaction_date)
- Index pada hierarchy_path untuk tree queries

### Caching Strategy
- Cache cost center hierarchy tree (TTL: 1 hour, invalidate on update)
- Cache active cost centers list (TTL: 1 hour, invalidate on update)
- Cache allocation rules (TTL: 1 day, invalidate on update)
- Cache budget data (TTL: 1 hour, invalidate on transaction)
- Use Laravel cache tags untuk selective invalidation

### Query Optimization
- Eager loading untuk relationships (parent, children, organization_unit)
- Use hierarchy_path untuk efficient tree queries
- Batch insert untuk allocation journals
- Use database transactions untuk allocation process
- Pagination untuk large result sets
- Use database views untuk complex reporting queries

### Allocation Process Optimization
- Process allocations in batches (chunk size: 1000)
- Use queue jobs untuk long-running allocation processes
- Implement progress tracking untuk real-time status updates
- Use database locks untuk prevent concurrent allocation runs
- Implement retry mechanism dengan exponential backoff

## Security Considerations

### Authentication & Authorization
- All endpoints require authentication
- Permission-based access control:
  - `cost-center-management.view`: View cost centers
  - `cost-center-management.create`: Create cost centers
  - `cost-center-management.edit`: Edit cost centers
  - `cost-center-management.delete`: Delete cost centers
  - `cost-center-management.allocate`: Execute allocations
  - `cost-center-management.approve`: Approve allocation rules
- Row-level security: Cost center managers can only view/edit their own cost centers

### Data Protection
- Audit logging untuk semua operasi
- Soft deletes untuk cost centers (preserve history)
- Encryption untuk sensitive data (budget amounts, formulas)
- Input sanitization untuk prevent SQL injection
- CSRF protection untuk all forms

### Compliance
- Audit trail retention: 5 years minimum
- Data export untuk compliance reporting
- Role-based access untuk sensitive operations
- Approval workflow untuk critical changes

## Deployment Considerations

### Database Migrations
- Migrations harus idempotent
- Use transactions untuk complex migrations
- Provide rollback scripts
- Test migrations pada staging environment

### Seeding
- Seed default cost center types
- Seed sample allocation rules untuk testing
- Seed sample cost centers untuk demo

### Configuration
- Configurable allocation batch size
- Configurable budget threshold percentages
- Configurable cache TTL values
- Configurable audit log retention period

### Monitoring
- Monitor allocation process execution time
- Monitor database query performance
- Monitor cache hit rates
- Monitor event processing delays
- Alert on budget threshold exceeded
- Alert on allocation validation failures
