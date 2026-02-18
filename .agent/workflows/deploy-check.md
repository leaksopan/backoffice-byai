---
description: Checklist sebelum deploy atau merge ke branch utama
---

# Deploy / Merge Checklist

> Checklist detail lengkap: `.ai/checklist.md`

## Pre-deploy Commands

// turbo-all

1. Install dependencies
```bash
composer install --no-dev --optimize-autoloader
```

2. Jalankan migration
```bash
php artisan migrate --force
```

3. Build frontend assets
```bash
npm run build
```

4. Jalankan tests
```bash
php artisan test
```

5. Clear & cache config
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Checklist Manual

### A) Setup & Packages
- [ ] `.env` production sudah dikonfigurasi (DB, APP_URL, APP_ENV=production)
- [ ] `APP_DEBUG=false` di production
- [ ] `php artisan key:generate` sudah dijalankan

### B) DB Schema
- [ ] Semua migration sudah jalan tanpa error
- [ ] Tabel `modules`, `module_menus`, `module_forms` ada

### C) Auth & Middleware
- [ ] Login redirect ke `/dashboard-modules`
- [ ] Middleware `EnsureModuleAccess` berfungsi (404/403)
- [ ] Semua route modul pakai `auth` + `EnsureModuleAccess`

### D) Modules & Permissions
- [ ] Semua modul ter-seed di tabel `modules`
- [ ] Permission `access {moduleKey}` + CRUD permissions dibuat
- [ ] Admin role punya semua permissions

### E) UI & Layout
- [ ] Module dashboard menampilkan modul sesuai permission
- [ ] Sidebar menus berfungsi per modul
- [ ] Filament `/admin` hanya bisa diakses role `admin`

### F) API (jika ada JSON endpoints)
- [ ] Semua response JSON pakai wrapper `success/message/data/meta`
- [ ] Validation error return 422 + `errors`
