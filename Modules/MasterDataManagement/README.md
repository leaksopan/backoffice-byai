# Master Data Management Module (M02)

Modul pengelolaan data referensi untuk seluruh sistem ERP BLUD.

## Fitur Utama

1. **Struktur Organisasi** - Pengelolaan hierarki unit organisasi BLUD
2. **Chart of Accounts (COA)** - Bagan akun sesuai standar BLUD
3. **Sumber Dana** - Pengelolaan sumber dana (APBN, APBD, PNBP, dll)
4. **Katalog Layanan** - Data master layanan medis dan non-medis
5. **Tarif Layanan** - Pengelolaan tarif dengan versioning
6. **SDM** - Data master sumber daya manusia
7. **Aset** - Data master aset dan inventaris

## Permissions

- `access master-data-management` - Akses ke modul
- `master-data-management.view` - Lihat data
- `master-data-management.create` - Buat data baru
- `master-data-management.edit` - Edit data
- `master-data-management.delete` - Hapus data

## Routes

- Dashboard: `/m/master-data-management/dashboard`
- Prefix: `mdm.*`

## Installation

Module sudah ter-register otomatis. Jalankan seeder:

```bash
php artisan db:seed --class=MasterDataManagementModuleSeeder
```

## Tech Stack

- Laravel 12
- Filament v3 (Admin Panel)
- Livewire v3 (Interactive UI)
- Spatie Laravel Permission v6
