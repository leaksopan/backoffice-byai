# Module Creation Checklist

Checklist ini WAJIB diikuti setiap kali membuat modul baru untuk menghindari error berulang.

## 1. Module Definition & Registration

- [ ] Tambahkan module ke `$moduleDefinitions` di `database/seeders/DatabaseSeeder.php`
  ```php
  'module-key' => [
      'name' => 'Module Name',
      'description' => 'Module description',
      'icon' => 'heroicon-o-icon-name',
      'entry_route' => 'prefix.dashboard',
      'sort' => X,
      'is_active' => true,
  ]
  ```

## 2. Menu Registration

- [ ] Tambahkan menu ke `$menusByModule` di `database/seeders/DatabaseSeeder.php`
  ```php
  'module-key' => [
      [
          'label' => 'Dashboard',
          'route_name' => 'prefix.dashboard',
          'icon' => 'heroicon-o-home',
          'sort_order' => 1,
          'permission_name' => 'module-key.view',
          'section' => 'MAIN', // atau 'ADMIN'
          'is_active' => true,
      ],
      // ... menu lainnya
  ]
  ```

## 3. Controller Rules

- [ ] **JANGAN PERNAH** pakai `$this->middleware()` di `__construct()`
- [ ] Namespace HARUS: `Modules\{ModuleName}\Http\Controllers`
- [ ] BUKAN: `Modules\{ModuleName}\app\Http\Controllers`
- [ ] Middleware handled by routes, bukan controller

## 4. Routes

- [ ] Prefix: `/m/{module-key}`
- [ ] Middleware: `['auth', EnsureModuleAccess::class]`
- [ ] Semua route WAJIB: `->defaults('moduleKey', 'module-key')`
- [ ] Route name pattern: `prefix.feature`
- [ ] **JANGAN** redirect ke `/admin` - semua CRUD di modul

## 5. Views & CRUD

- [ ] **GUNAKAN FILAMENT** untuk semua CRUD operations
- [ ] Filament Resources di: `Modules/{ModuleName}/app/Filament/Resources/`
- [ ] Filament accessible via module routes (embed di `/m/` prefix)
- [ ] Custom views (dashboard, reports) tetap pakai Blade
- [ ] Livewire untuk interactive components (charts, real-time data)

## 6. Filament Integration in Modules

- [ ] Create Filament Resource: `php artisan make:filament-resource {Model} --generate`
- [ ] Resource location: `Modules/{ModuleName}/app/Filament/Resources/`
- [ ] Register Filament panel di module ServiceProvider
- [ ] Embed Filament pages di module layout dengan iframe atau direct integration
- [ ] **SEMUA USER** bisa akses Filament CRUD lewat module, bukan cuma admin

## 6. Permissions

- [ ] `access {module-key}` - akses modul
- [ ] `{module-key}.view` - lihat data
- [ ] `{module-key}.create` - buat data
- [ ] `{module-key}.edit` - edit data
- [ ] `{module-key}.delete` - hapus data

## 7. Seeder Execution

- [ ] Jalankan: `php artisan db:seed --class=DatabaseSeeder`
- [ ] Verify menu muncul di database
- [ ] Verify permission ter-assign ke role admin

## 8. Common Errors to Avoid

### Error: "Call to undefined method middleware()"
**Cause:** Pakai `$this->middleware()` di controller constructor
**Fix:** Hapus semua `$this->middleware()` dari constructor

### Error: "View [xxx] not found"
**Cause:** View file belum dibuat
**Fix:** Buat file blade sesuai path yang dipanggil di controller

### Error: "Class does not exist"
**Cause:** Namespace controller salah (ada `app` di tengah)
**Fix:** Namespace: `Modules\{ModuleName}\Http\Controllers`

### Error: Navbar hilang
**Cause:** Menu belum di-seed ke database
**Fix:** Tambahkan menu ke DatabaseSeeder dan run seeder

## 9. Testing Checklist

- [ ] Route list: `php artisan route:list --name=prefix`
- [ ] Access module URL: `/m/{module-key}`
- [ ] Navbar muncul dengan semua menu
- [ ] Semua menu link bisa diklik tanpa error
- [ ] Permission check berfungsi

## 10. File Structure Verification

```
Modules/{ModuleName}/
├── app/
│   └── Http/
│       └── Controllers/     ← Controller di sini
│           └── XxxController.php
├── resources/
│   └── views/              ← View di sini
│       ├── dashboard.blade.php
│       └── {feature}/
│           └── index.blade.php
└── Routes/
    └── web.php             ← Routes di sini
```

## Quick Reference

**Module Key Format:** kebab-case (e.g., `cost-center-management`)
**Route Prefix:** pendek (e.g., `ccm`, `mdm`, `sm`)
**Section Values:** `MAIN` atau `ADMIN` (uppercase)
**Column Names:** `sort_order` bukan `sort`, `section` bukan `group`
**CRUD Tool:** Filament v3 (embedded di modul)
**Custom Views:** Blade + Tailwind + Livewire
**Filament Access:** Semua user via `/m/{module-key}`, bukan cuma admin

## Filament Usage Strategy

**Filament untuk:**
- CRUD operations (auto-generated forms, tables, filters)
- Data management yang standard
- Quick admin interfaces

**Blade + Livewire untuk:**
- Custom dashboards
- Complex business workflows
- Reports & analytics
- Approval processes
- Interactive charts

**Integration:**
- Filament Resources di `Modules/{ModuleName}/app/Filament/Resources/`
- Accessible via module routes dengan proper permission checks
- Embedded dalam module layout (bukan standalone `/admin`)
