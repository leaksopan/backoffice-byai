# Cost Center Management Seeders

## Overview

Seeders untuk modul Cost Center Management yang menyediakan data awal dan sample data untuk development dan testing.

## Available Seeders

### 1. CostCenterManagementModuleSeeder

Seeder utama untuk setup modul:
- **Permissions**: Membuat 8 permissions untuk akses kontrol
  - `access cost-center-management`
  - `cost-center-management.view`
  - `cost-center-management.view-all`
  - `cost-center-management.create`
  - `cost-center-management.edit`
  - `cost-center-management.delete`
  - `cost-center-management.allocate`
  - `cost-center-management.approve`

- **Module Entry**: Membuat entry di tabel `modules`
  - Key: `cost-center-management`
  - Route: `ccm.dashboard`
  - Icon: `heroicon-o-building-office-2`

- **Module Menus**: Membuat 7 menu items
  - Dashboard
  - Cost Center Dashboard (Analytics)
  - Cost Centers (Master Data)
  - Allocation Rules (Master Data)
  - Allocation Process (Operations)
  - Approval - Allocation Rules
  - Approval - Budget Revisions

### 2. CostCenterSampleDataSeeder

Seeder untuk sample data development:

#### Cost Centers (12 units)
**Administrative (4):**
- CC-ADM-FIN: Keuangan
- CC-ADM-HR: SDM
- CC-ADM-IT: IT
- CC-ADM-GEN: Umum

**Medical (4):**
- CC-MED-RJ: Rawat Jalan
- CC-MED-RI: Rawat Inap
- CC-MED-IGD: IGD
- CC-MED-OK: Operasi

**Non-Medical (4):**
- CC-NM-LAB: Laboratorium
- CC-NM-RAD: Radiologi
- CC-NM-FAR: Farmasi
- CC-NM-GIZ: Gizi

#### Allocation Rules (3)
- **AR-FIN-001**: Alokasi biaya keuangan (percentage-based, 6 targets)
- **AR-IT-001**: Alokasi biaya IT (headcount-based)
- **AR-GEN-001**: Alokasi biaya umum (square_footage-based)

#### Cost Pools (2)
- **CP-UTL-001**: Utilities Pool (listrik, air, gas)
- **CP-IT-001**: IT Services Pool

#### Service Lines (4)
- **SL-RJ-001**: Layanan Rawat Jalan
- **SL-RI-001**: Layanan Rawat Inap
- **SL-IGD-001**: Layanan Gawat Darurat
- **SL-OK-001**: Layanan Bedah (shared dengan penunjang)

#### Budgets
- Budget untuk semua 12 cost centers
- 12 bulan (current year)
- 5 kategori per bulan: personnel, supplies, services, depreciation, overhead
- Total: 720 budget records

### 3. CostCenterManagementDatabaseSeeder

Main seeder yang memanggil semua seeders di atas.

## Usage

### Run All Seeders
```bash
php artisan module:seed CostCenterManagement
```

### Run Specific Seeder
```bash
php artisan db:seed --class="Modules\\CostCenterManagement\\Database\\Seeders\\CostCenterManagementModuleSeeder"
php artisan db:seed --class="Modules\\CostCenterManagement\\Database\\Seeders\\CostCenterSampleDataSeeder"
```

### Run from Main DatabaseSeeder
Add to `database/seeders/DatabaseSeeder.php`:
```php
$this->call([
    \Modules\CostCenterManagement\Database\Seeders\CostCenterManagementDatabaseSeeder::class,
]);
```

## Prerequisites

Sebelum menjalankan seeders, pastikan:

1. **Master Data Management sudah di-seed** dengan minimal:
   - 12 organization units (id 1-12)
   - User dengan id 1 (untuk manager_user_id)

2. **Migrations sudah dijalankan**:
   ```bash
   php artisan migrate
   ```

3. **Spatie Permission sudah di-setup**:
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   ```

## Notes

- Semua seeders menggunakan `firstOrCreate()` untuk mencegah duplicate data
- Sample data menggunakan `DB::transaction()` untuk atomicity
- Organization unit IDs (1-12) adalah placeholder - sesuaikan dengan data MDM yang sebenarnya
- Budget amounts dalam satuan Rupiah (sudah dikalikan 1,000,000)
- Semua cost centers dibuat dengan status `is_active = true`
- Allocation rules dibuat dengan status `approval_status = 'approved'`

## Customization

Untuk menyesuaikan sample data:

1. Edit `seedCostCenters()` untuk mengubah cost center data
2. Edit `seedAllocationRules()` untuk mengubah allocation rules
3. Edit `seedBudgets()` untuk mengubah budget amounts
4. Sesuaikan `organization_unit_id` dengan data MDM yang sebenarnya

## Testing

Setelah seeding, verifikasi dengan:

```bash
# Check cost centers
php artisan tinker
>>> \Modules\CostCenterManagement\Models\CostCenter::count()
>>> \Modules\CostCenterManagement\Models\AllocationRule::count()
>>> \Modules\CostCenterManagement\Models\CostCenterBudget::count()
```

Expected counts:
- Cost Centers: 12
- Allocation Rules: 3
- Allocation Rule Targets: 6 (untuk AR-FIN-001)
- Cost Pools: 2
- Service Lines: 4
- Budgets: 720 (12 cost centers × 12 months × 5 categories)
