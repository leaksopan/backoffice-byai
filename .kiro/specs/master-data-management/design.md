# Design Document - Master Data Management (M02)

## Overview

Modul Master Data Management (MDM) adalah sistem pengelolaan data referensi untuk seluruh sistem ERP BLUD. Modul ini dibangun menggunakan Laravel 12 dengan arsitektur modular (nwidart/laravel-modules), Filament v3 untuk admin panel, dan Livewire v3 untuk interaksi real-time. MDM menyediakan CRUD interface untuk 7 kategori data master utama: Struktur Organisasi, Chart of Accounts, Sumber Dana, Katalog Layanan, Tarif, SDM, dan Aset.

## Architecture

### Module Structure
```
Modules/MasterDataManagement/
├── app/
│   ├── Http/Controllers/
│   │   ├── MdmDashboardController.php
│   │   ├── OrganizationUnitController.php
│   │   ├── ChartOfAccountController.php
│   │   ├── FundingSourceController.php
│   │   ├── ServiceCatalogController.php
│   │   ├── TariffController.php
│   │   ├── HumanResourceController.php
│   │   └── AssetController.php
│   ├── Models/
│   │   ├── MdmOrganizationUnit.php
│   │   ├── MdmChartOfAccount.php
│   │   ├── MdmFundingSource.php
│   │   ├── MdmServiceCatalog.php
│   │   ├── MdmTariff.php
│   │   ├── MdmTariffBreakdown.php
│   │   ├── MdmHumanResource.php
│   │   ├── MdmHrAssignment.php
│   │   ├── MdmAsset.php
│   │   └── MdmAssetMovement.php
│   ├── Services/
│   │   ├── OrganizationHierarchyService.php
│   │   ├── CoaValidationService.php
│   │   ├── TariffCalculationService.php
│   │   └── AssetDepreciationService.php
│   └── Providers/
│       └── MasterDataManagementServiceProvider.php
├── database/migrations/
├── resources/views/
├── Routes/web.php
└── tests/
```

### Technology Stack
- Framework: Laravel 12
- Admin Panel: Filament v3 (untuk CRUD data master)
- Interactive UI: Livewire v3 (untuk tree view organisasi, tariff calculator)
- Database: SQLite (development), PostgreSQL/MySQL (production)
- Validation: Laravel Form Requests
- Authorization: Spatie Laravel Permission v6

### Module Configuration
- Module Key: `master-data-management`
- Route Prefix: `mdm`
- Permission Prefix: `master-data-management`

## Components and Interfaces

### 1. Organization Unit Management

**Controller:** `OrganizationUnitController`
**Model:** `MdmOrganizationUnit`

**Methods:**
```php
class OrganizationUnitController extends Controller
{
    public function index(): View
    public function create(): View
    public function store(StoreOrganizationUnitRequest $request): RedirectResponse
    public function edit(MdmOrganizationUnit $unit): View
    public function update(UpdateOrganizationUnitRequest $request, MdmOrganizationUnit $unit): RedirectResponse
    public function destroy(MdmOrganizationUnit $unit): RedirectResponse
    public function tree(): JsonResponse // untuk visualisasi hierarki
}
```

**Service:** `OrganizationHierarchyService`
```php
class OrganizationHierarchyService
{
    public function validateNoCircularReference(int $unitId, ?int $parentId): bool
    public function updateHierarchyPath(MdmOrganizationUnit $unit): void
    public function getDescendants(int $unitId): Collection
    public function canDelete(MdmOrganizationUnit $unit): bool
}
```

### 2. Chart of Accounts Management

**Controller:** `ChartOfAccountController`
**Model:** `MdmChartOfAccount`

**Methods:**
```php
class ChartOfAccountController extends Controller
{
    public function index(Request $request): View // filter by category, status
    public function create(): View
    public function store(StoreChartOfAccountRequest $request): RedirectResponse
    public function edit(MdmChartOfAccount $account): View
    public function update(UpdateChartOfAccountRequest $request, MdmChartOfAccount $account): RedirectResponse
    public function destroy(MdmChartOfAccount $account): RedirectResponse
    public function export(): BinaryFileResponse // export to Excel
    public function import(ImportCoaRequest $request): RedirectResponse
}
```

**Service:** `CoaValidationService`
```php
class CoaValidationService
{
    public function validateCoaFormat(string $code): bool
    public function canPostTransaction(MdmChartOfAccount $account): bool
    public function canDelete(MdmChartOfAccount $account): bool
    public function parseCoaStructure(string $code): array
}
```

### 3. Funding Source Management

**Controller:** `FundingSourceController`
**Model:** `MdmFundingSource`

**Methods:**
```php
class FundingSourceController extends Controller
{
    public function index(): View
    public function create(): View
    public function store(StoreFundingSourceRequest $request): RedirectResponse
    public function edit(MdmFundingSource $source): View
    public function update(UpdateFundingSourceRequest $request, MdmFundingSource $source): RedirectResponse
    public function destroy(MdmFundingSource $source): RedirectResponse
    public function checkAvailability(int $sourceId, Carbon $date): JsonResponse
}
```

### 4. Service Catalog Management

**Controller:** `ServiceCatalogController`
**Model:** `MdmServiceCatalog`

**Methods:**
```php
class ServiceCatalogController extends Controller
{
    public function index(Request $request): View // filter by category, unit
    public function create(): View
    public function store(StoreServiceCatalogRequest $request): RedirectResponse
    public function edit(MdmServiceCatalog $service): View
    public function update(UpdateServiceCatalogRequest $request, MdmServiceCatalog $service): RedirectResponse
    public function destroy(MdmServiceCatalog $service): RedirectResponse
    public function searchByCode(string $code): JsonResponse
}
```

### 5. Tariff Management

**Controller:** `TariffController`
**Model:** `MdmTariff`, `MdmTariffBreakdown`

**Methods:**
```php
class TariffController extends Controller
{
    public function index(Request $request): View // filter by service, class, period
    public function create(): View
    public function store(StoreTariffRequest $request): RedirectResponse
    public function edit(MdmTariff $tariff): View
    public function update(UpdateTariffRequest $request, MdmTariff $tariff): RedirectResponse
    public function destroy(MdmTariff $tariff): RedirectResponse
    public function getApplicableTariff(int $serviceId, string $class, Carbon $date): JsonResponse
    public function history(int $serviceId): View // tariff version history
}
```

**Service:** `TariffCalculationService`
```php
class TariffCalculationService
{
    public function getApplicableTariff(int $serviceId, string $class, Carbon $date): ?MdmTariff
    public function validateNoPeriodOverlap(int $serviceId, string $class, Carbon $startDate, Carbon $endDate, ?int $excludeTariffId = null): bool
    public function calculateTotalTariff(MdmTariff $tariff): float
}
```

### 6. Human Resource Management

**Controller:** `HumanResourceController`
**Model:** `MdmHumanResource`, `MdmHrAssignment`

**Methods:**
```php
class HumanResourceController extends Controller
{
    public function index(Request $request): View // filter by category, unit, status
    public function create(): View
    public function store(StoreHumanResourceRequest $request): RedirectResponse
    public function edit(MdmHumanResource $hr): View
    public function update(UpdateHumanResourceRequest $request, MdmHumanResource $hr): RedirectResponse
    public function destroy(MdmHumanResource $hr): RedirectResponse
    public function assignments(MdmHumanResource $hr): View
    public function storeAssignment(StoreHrAssignmentRequest $request, MdmHumanResource $hr): RedirectResponse
}
```

### 7. Asset Management

**Controller:** `AssetController`
**Model:** `MdmAsset`, `MdmAssetMovement`

**Methods:**
```php
class AssetController extends Controller
{
    public function index(Request $request): View // filter by category, location, status
    public function create(): View
    public function store(StoreAssetRequest $request): RedirectResponse
    public function edit(MdmAsset $asset): View
    public function update(UpdateAssetRequest $request, MdmAsset $asset): RedirectResponse
    public function destroy(MdmAsset $asset): RedirectResponse
    public function move(MoveAssetRequest $request, MdmAsset $asset): RedirectResponse
    public function depreciationReport(): View
}
```

**Service:** `AssetDepreciationService`
```php
class AssetDepreciationService
{
    public function calculateMonthlyDepreciation(MdmAsset $asset): float
    public function calculateAccumulatedDepreciation(MdmAsset $asset, Carbon $asOfDate): float
    public function getBookValue(MdmAsset $asset, Carbon $asOfDate): float
}
```

## Data Models

### MdmOrganizationUnit
```sql
CREATE TABLE mdm_organization_units (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('installation', 'department', 'unit', 'section') NOT NULL,
    parent_id BIGINT NULL,
    hierarchy_path TEXT NULL, -- untuk query hierarki cepat
    level INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES mdm_organization_units(id) ON DELETE RESTRICT,
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active),
    INDEX idx_type (type)
);
```

### MdmChartOfAccount
```sql
CREATE TABLE mdm_chart_of_accounts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL, -- format: X-XX-XX-XX-XXX
    name VARCHAR(255) NOT NULL,
    category ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    normal_balance ENUM('debit', 'credit') NOT NULL,
    parent_id BIGINT NULL,
    level INT NOT NULL,
    is_header BOOLEAN DEFAULT FALSE, -- header tidak bisa diposting
    is_active BOOLEAN DEFAULT TRUE,
    external_code VARCHAR(50) NULL, -- untuk mapping SIMDA/SIPD
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES mdm_chart_of_accounts(id) ON DELETE RESTRICT,
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_parent (parent_id)
);
```

### MdmFundingSource
```sql
CREATE TABLE mdm_funding_sources (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('apbn', 'apbd_provinsi', 'apbd_kab_kota', 'pnbp', 'hibah', 'pinjaman', 'lainnya') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_active (is_active),
    INDEX idx_period (start_date, end_date)
);
```

### MdmServiceCatalog
```sql
CREATE TABLE mdm_service_catalogs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category ENUM('rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi', 'persalinan', 'administrasi') NOT NULL,
    unit_id BIGINT NOT NULL, -- unit penyedia layanan
    inacbg_code VARCHAR(50) NULL,
    standard_duration INT NULL, -- dalam menit
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES mdm_organization_units(id) ON DELETE RESTRICT,
    INDEX idx_category (category),
    INDEX idx_unit (unit_id),
    INDEX idx_active (is_active)
);
```

### MdmTariff
```sql
CREATE TABLE mdm_tariffs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    service_class ENUM('vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum') NOT NULL,
    tariff_amount DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    payer_type VARCHAR(50) NULL, -- 'umum', 'bpjs', 'asuransi_x'
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES mdm_service_catalogs(id) ON DELETE RESTRICT,
    INDEX idx_service_class (service_id, service_class),
    INDEX idx_period (start_date, end_date),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_tariff_period (service_id, service_class, payer_type, start_date, end_date)
);
```

### MdmTariffBreakdown
```sql
CREATE TABLE mdm_tariff_breakdowns (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tariff_id BIGINT NOT NULL,
    component_type ENUM('jasa_medis', 'jasa_sarana', 'bmhp', 'obat', 'administrasi') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    percentage DECIMAL(5,2) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tariff_id) REFERENCES mdm_tariffs(id) ON DELETE CASCADE,
    INDEX idx_tariff (tariff_id)
);
```

### MdmHumanResource
```sql
CREATE TABLE mdm_human_resources (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category ENUM('medis_dokter', 'medis_perawat', 'medis_bidan', 'penunjang_medis', 'administrasi', 'umum') NOT NULL,
    position VARCHAR(100) NOT NULL,
    employment_status ENUM('pns', 'pppk', 'kontrak', 'honorer') NOT NULL,
    grade VARCHAR(10) NULL,
    basic_salary DECIMAL(15,2) NULL,
    effective_hours_per_week INT NULL, -- untuk cost rate calculation
    is_active BOOLEAN DEFAULT TRUE,
    hire_date DATE NULL,
    termination_date DATE NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);
```

### MdmHrAssignment
```sql
CREATE TABLE mdm_hr_assignments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    hr_id BIGINT NOT NULL,
    unit_id BIGINT NOT NULL,
    allocation_percentage DECIMAL(5,2) NOT NULL, -- 0-100
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (hr_id) REFERENCES mdm_human_resources(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES mdm_organization_units(id) ON DELETE RESTRICT,
    INDEX idx_hr (hr_id),
    INDEX idx_unit (unit_id),
    INDEX idx_period (start_date, end_date)
);
```

### MdmAsset
```sql
CREATE TABLE mdm_assets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category ENUM('tanah', 'gedung', 'peralatan_medis', 'peralatan_non_medis', 'kendaraan', 'inventaris') NOT NULL,
    acquisition_value DECIMAL(15,2) NOT NULL,
    acquisition_date DATE NOT NULL,
    useful_life_years INT NULL, -- umur ekonomis
    depreciation_method ENUM('straight_line', 'declining_balance', 'units_of_production') NULL,
    residual_value DECIMAL(15,2) DEFAULT 0,
    current_location_id BIGINT NULL, -- unit organisasi
    condition ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT 'baik',
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_by BIGINT NULL,
    updated_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (current_location_id) REFERENCES mdm_organization_units(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_location (current_location_id),
    INDEX idx_active (is_active)
);
```

### MdmAssetMovement
```sql
CREATE TABLE mdm_asset_movements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    asset_id BIGINT NOT NULL,
    from_location_id BIGINT NULL,
    to_location_id BIGINT NOT NULL,
    movement_date DATE NOT NULL,
    reason TEXT NULL,
    approved_by BIGINT NULL,
    created_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES mdm_assets(id) ON DELETE CASCADE,
    FOREIGN KEY (from_location_id) REFERENCES mdm_organization_units(id) ON DELETE SET NULL,
    FOREIGN KEY (to_location_id) REFERENCES mdm_organization_units(id) ON DELETE RESTRICT,
    INDEX idx_asset (asset_id),
    INDEX idx_date (movement_date)
);
```

## Correctness Properties

*Property adalah karakteristik atau perilaku yang harus berlaku untuk semua eksekusi sistem yang valid - pada dasarnya, pernyataan formal tentang apa yang harus dilakukan sistem. Property berfungsi sebagai jembatan antara spesifikasi yang dapat dibaca manusia dan jaminan kebenaran yang dapat diverifikasi mesin.*


### Property Reflection

Setelah menganalisis semua acceptance criteria, berikut adalah properties yang teridentifikasi:

**Testable Properties:**
- 1.1, 1.2, 1.3, 1.4, 1.6 (Organization Unit)
- 2.2, 2.3, 2.6 (Chart of Accounts)
- 3.2, 3.3, 3.4, 3.6 (Funding Source)
- 4.2, 4.3, 4.4 (Service Catalog)
- 5.1, 5.3, 5.6 (Tariff)
- 6.2, 6.6, 6.7 (Human Resource)
- 7.2, 7.3, 7.4, 7.6 (Asset)
- 8.5, 8.7 (Integration)
- 9.1 (Validation)
- 10.2, 10.6 (Security)

**Redundancy Analysis:**
1. Properties 3.6, 1.6, 6.7 semua tentang "non-aktif mencegah penggunaan baru" - bisa digabung menjadi satu property umum
2. Properties 3.3, 4.3, 6.2 semua tentang uniqueness constraint - bisa digabung menjadi satu property umum
3. Properties 1.1, 3.2, 4.2, 7.2 semua tentang "menyimpan data dengan field lengkap" - ini adalah basic CRUD, tidak perlu property terpisah
4. Property 8.5 adalah generalisasi dari 1.3, 2.6 - bisa digabung
5. Property 5.3 dan 3.4 keduanya tentang validasi periode overlap - bisa digabung konsepnya

**Final Properties (setelah eliminasi redundansi):**
1. Circular reference validation (1.2)
2. Hierarchy path consistency (1.4)
3. Referential integrity protection (1.3, 2.6, 8.5 - digabung)
4. Inactive entity prevention (1.6, 3.6, 6.7 - digabung)
5. COA format validation (2.2)
6. Header account posting prevention (2.3)
7. Unique code constraint (3.3, 4.3, 6.2 - digabung)
8. Period validity check (3.4, 5.3 - digabung)
9. Tariff period overlap prevention (5.3 - specific case)
10. Applicable tariff retrieval (5.6)
11. HR allocation percentage limit (6.6)
12. Asset depreciation calculation (7.3, 7.4 - digabung)
13. Asset movement tracking (7.6)
14. Export-import round trip (8.7)
15. Mandatory field validation (9.1)
16. Permission-based access control (10.2)

### Correctness Properties

Property 1: Circular Reference Prevention
*For any* organization unit and proposed parent unit, setting the parent relationship should be rejected if it would create a circular reference in the hierarchy
**Validates: Requirements 1.2**

Property 2: Hierarchy Path Consistency
*For any* organization unit, when its parent is changed, all descendant units should have their hierarchy paths updated to reflect the new structure
**Validates: Requirements 1.4**

Property 3: Referential Integrity Protection
*For any* master data entity (organization unit, COA, funding source, service, tariff, HR, asset), if it is referenced by other entities or transactions, deletion should be prevented
**Validates: Requirements 1.3, 2.6, 8.5**

Property 4: Inactive Entity Prevention
*For any* master data entity with is_active=false, the system should reject its use in new transactions or assignments
**Validates: Requirements 1.6, 3.6, 6.7**

Property 5: COA Format Validation
*For any* chart of account code input, the system should accept only codes matching the format X-XX-XX-XX-XXX where X represents digits
**Validates: Requirements 2.2**

Property 6: Header Account Posting Prevention
*For any* chart of account that has child accounts (is_header=true), the system should reject direct transaction postings to that account
**Validates: Requirements 2.3**

Property 7: Unique Code Constraint
*For any* master data entity type (funding source, service catalog, human resource, asset), the system should reject creation or update if the code already exists within that entity type
**Validates: Requirements 3.3, 4.3, 6.2**

Property 8: Period Validity Check
*For any* master data entity with a validity period (start_date, end_date), the system should reject its use for transactions outside that period
**Validates: Requirements 3.4**

Property 9: Tariff Period Overlap Prevention
*For any* combination of service_id, service_class, and payer_type, the system should reject tariff creation or update if the validity period overlaps with an existing active tariff for the same combination
**Validates: Requirements 5.3**

Property 10: Applicable Tariff Retrieval
*For any* combination of service_id, service_class, payer_type, and transaction_date, the system should return the unique tariff where transaction_date falls between start_date and end_date (or end_date is null) and is_active=true
**Validates: Requirements 5.6**

Property 11: HR Allocation Percentage Limit
*For any* human resource, the sum of allocation_percentage across all active assignments should not exceed 100%
**Validates: Requirements 6.6**

Property 12: Asset Depreciation Calculation
*For any* asset with useful_life_years > 0 and depreciation_method specified, the monthly depreciation amount should be calculated correctly according to the chosen method (straight_line: (acquisition_value - residual_value) / (useful_life_years * 12), declining_balance: book_value * rate / 12)
**Validates: Requirements 7.3, 7.4**

Property 13: Asset Movement Tracking
*For any* asset movement, the system should create a record in mdm_asset_movements with from_location_id (previous location), to_location_id (new location), and update the asset's current_location_id
**Validates: Requirements 7.6**

Property 14: Export-Import Round Trip
*For any* master data entity collection exported to Excel or JSON format, importing the exported data should result in equivalent entities (same codes, names, and key attributes)
**Validates: Requirements 8.7**

Property 15: Mandatory Field Validation
*For any* master data entity creation or update, the system should reject the operation if any mandatory field (as defined by business rules for that entity type) is null or empty
**Validates: Requirements 9.1**

Property 16: Permission-Based Access Control
*For any* user attempting to access master data operations (view, create, edit, delete), the system should grant access only if the user has the corresponding permission (master-data-management.{operation})
**Validates: Requirements 10.2**

## Error Handling

### Validation Errors
- Format validation errors: Return HTTP 422 dengan detail field yang invalid
- Uniqueness constraint violations: Return HTTP 409 dengan pesan "Kode sudah digunakan"
- Referential integrity violations: Return HTTP 409 dengan pesan "Data tidak dapat dihapus karena masih digunakan"
- Period overlap: Return HTTP 422 dengan detail periode yang overlap

### Business Logic Errors
- Circular reference: Return HTTP 422 dengan pesan "Tidak dapat menetapkan parent karena akan membuat circular reference"
- Inactive entity usage: Return HTTP 422 dengan pesan "Data tidak aktif dan tidak dapat digunakan"
- Header account posting: Return HTTP 422 dengan pesan "Akun header tidak dapat digunakan untuk posting transaksi"
- Allocation percentage exceeded: Return HTTP 422 dengan pesan "Total alokasi melebihi 100%"

### Authorization Errors
- Missing permission: Return HTTP 403 dengan pesan "Anda tidak memiliki akses untuk operasi ini"
- Module not active: Return HTTP 403 dengan pesan "Modul tidak aktif"

### System Errors
- Database errors: Log error detail, return HTTP 500 dengan pesan generic
- External API errors: Log error, return HTTP 503 dengan pesan "Layanan eksternal tidak tersedia"

## Testing Strategy

### Unit Tests
Unit tests akan fokus pada:
- Validation logic untuk format kode (COA, NIP, kode aset)
- Calculation logic untuk depresiasi aset
- Business rules untuk circular reference detection
- Permission checking logic
- Specific edge cases seperti:
  - Empty input handling
  - Boundary values untuk percentage (0%, 100%)
  - Date boundary conditions

### Property-Based Tests
Property tests akan menggunakan library PHPUnit dengan extension untuk property-based testing atau Pest PHP. Setiap test akan run minimum 100 iterations.

**Test Configuration:**
- Framework: PHPUnit / Pest PHP
- Minimum iterations: 100 per property test
- Tag format: `@test Feature: master-data-management, Property {N}: {property_text}`

**Property Test Coverage:**
1. Property 1-16 akan diimplementasikan sebagai property-based tests
2. Generators akan dibuat untuk:
   - Random organization units dengan hierarki
   - Random COA codes (valid dan invalid)
   - Random tariff dengan periode
   - Random HR assignments dengan alokasi
   - Random assets dengan depreciation parameters

**Integration Tests:**
- API endpoint testing untuk integrasi dengan modul lain
- Export-import round trip testing
- Multi-user concurrent access testing
- Database transaction rollback testing

### Test Data Generators
```php
// Generator untuk organization unit
function generateOrganizationUnit(): array {
    return [
        'code' => 'ORG' . rand(1000, 9999),
        'name' => 'Unit ' . Str::random(10),
        'type' => Arr::random(['installation', 'department', 'unit', 'section']),
        'is_active' => rand(0, 1) === 1,
    ];
}

// Generator untuk COA code (valid format)
function generateValidCoaCode(): string {
    return sprintf(
        '%d-%02d-%02d-%02d-%03d',
        rand(1, 9),
        rand(1, 99),
        rand(1, 99),
        rand(1, 99),
        rand(1, 999)
    );
}

// Generator untuk tariff dengan periode
function generateTariff(int $serviceId): array {
    $startDate = Carbon::now()->subDays(rand(0, 365));
    return [
        'service_id' => $serviceId,
        'service_class' => Arr::random(['vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum']),
        'tariff_amount' => rand(10000, 1000000),
        'start_date' => $startDate,
        'end_date' => rand(0, 1) ? $startDate->copy()->addDays(rand(30, 365)) : null,
        'is_active' => true,
    ];
}
```

## Integration with Other Modules

### Data Consumers
Modul-modul yang akan menggunakan data dari MDM:
1. **Cost Center Management (M03)**: Menggunakan organization units untuk cost center mapping
2. **Resource Cost Management (M04)**: Menggunakan HR data untuk salary costing, asset data untuk depreciation
3. **Budgeting System (M10)**: Menggunakan COA untuk budget line items, funding sources untuk budget allocation
4. **Accounting & Treasury (M11)**: Menggunakan COA untuk journal entries, funding sources untuk cash management
5. **Service Profitability (M08)**: Menggunakan service catalog dan tariff untuk revenue calculation

### API Endpoints for Integration
```php
// Organization Structure API
GET /api/mdm/organization-units
GET /api/mdm/organization-units/{id}
GET /api/mdm/organization-units/{id}/descendants
GET /api/mdm/organization-units/tree

// Chart of Accounts API
GET /api/mdm/chart-of-accounts
GET /api/mdm/chart-of-accounts/{id}
GET /api/mdm/chart-of-accounts/by-category/{category}
GET /api/mdm/chart-of-accounts/postable // hanya akun non-header

// Funding Sources API
GET /api/mdm/funding-sources
GET /api/mdm/funding-sources/{id}
GET /api/mdm/funding-sources/active-on/{date}

// Service Catalog API
GET /api/mdm/services
GET /api/mdm/services/{id}
GET /api/mdm/services/by-category/{category}
GET /api/mdm/services/by-unit/{unitId}

// Tariff API
GET /api/mdm/tariffs/applicable
  ?service_id={id}&class={class}&payer_type={type}&date={date}
GET /api/mdm/tariffs/{id}/breakdown

// Human Resource API
GET /api/mdm/human-resources
GET /api/mdm/human-resources/{id}
GET /api/mdm/human-resources/by-unit/{unitId}
GET /api/mdm/human-resources/{id}/assignments

// Asset API
GET /api/mdm/assets
GET /api/mdm/assets/{id}
GET /api/mdm/assets/by-location/{unitId}
GET /api/mdm/assets/{id}/depreciation-schedule
```

### Event Notifications
MDM akan emit events untuk perubahan data kritis:
```php
// Events yang akan di-dispatch
MasterDataCreated::class
MasterDataUpdated::class
MasterDataDeleted::class
MasterDataActivated::class
MasterDataDeactivated::class

// Event payload
[
    'entity_type' => 'organization_unit|coa|funding_source|service|tariff|hr|asset',
    'entity_id' => 123,
    'action' => 'created|updated|deleted|activated|deactivated',
    'changed_fields' => ['field1', 'field2'],
    'user_id' => 1,
    'timestamp' => '2026-02-15 10:30:00'
]
```

### Data Synchronization
- Real-time: Menggunakan Laravel Events untuk notifikasi perubahan
- Batch: Scheduled jobs untuk sinkronisasi data ke sistem eksternal (SIMDA, SIPD)
- Conflict resolution: Last-write-wins dengan audit trail

## Performance Considerations

### Database Indexing
- Index pada foreign keys untuk join performance
- Index pada frequently queried fields (code, is_active, dates)
- Composite index untuk tariff lookup (service_id, service_class, payer_type, start_date, end_date)
- Full-text index untuk search functionality

### Caching Strategy
- Cache organization hierarchy tree (TTL: 1 hour, invalidate on update)
- Cache active COA list (TTL: 1 hour, invalidate on update)
- Cache applicable tariffs (TTL: 1 day, invalidate on tariff update)
- Use Laravel cache tags untuk selective invalidation

### Query Optimization
- Eager loading untuk relationships (organization unit dengan parent, tariff dengan service)
- Pagination untuk list views (default 50 items per page)
- Database query caching untuk frequently accessed data
- Use database views untuk complex queries (e.g., active postable accounts)

## Security Considerations

### Data Access Control
- Row-level security: User hanya bisa akses data sesuai unit organisasi mereka
- Field-level security: Sensitive fields (salary) hanya visible untuk HR role
- Audit logging: Semua perubahan data dicatat dengan user, timestamp, old value, new value

### Data Validation
- Server-side validation untuk semua input
- SQL injection prevention menggunakan Eloquent ORM
- XSS prevention menggunakan Blade templating
- CSRF protection untuk semua form submissions

### Data Encryption
- Sensitive data (salary, personal info) encrypted at rest
- TLS/SSL untuk data in transit
- Secure key management menggunakan Laravel encryption

## Deployment Considerations

### Database Migration Strategy
- Migrations akan dibuat secara incremental
- Rollback plan untuk setiap migration
- Data seeding untuk initial setup (default COA, organization structure)
- Migration testing di staging environment sebelum production

### Module Activation
1. Run migrations: `php artisan migrate`
2. Seed initial data: `php artisan db:seed --class=MasterDataManagementSeeder`
3. Create permissions: `php artisan mdm:create-permissions`
4. Assign permissions to admin role
5. Activate module di tabel `modules`
6. Create menu entries di tabel `module_menus`

### Monitoring and Maintenance
- Log semua errors dan exceptions
- Monitor API response times
- Track data quality metrics (incomplete records, validation failures)
- Regular data cleanup untuk inactive records
- Backup strategy untuk master data
