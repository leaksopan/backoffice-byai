# BackOffice BLUD — Project Rules

> **Source of truth lengkap:** `.ai/standards.md` & `.ai/standards.json`
> **Checklist compliance:** `.ai/checklist.md`

---

## 1) Tech Stack

| Layer          | Teknologi                                    |
|----------------|----------------------------------------------|
| Framework      | Laravel 12 (Monolith + Modular Feature)      |
| Modular        | `nwidart/laravel-modules` v12                |
| Auth / UI      | Laravel Breeze (Blade + Tailwind CSS)        |
| Admin Panel    | Filament v3 — `/admin` — khusus role `admin` |
| RBAC           | `spatie/laravel-permission` v6               |
| Interaktif     | Livewire v3                                  |
| DB Dev         | SQLite (`database/database.sqlite`)          |

---

## 2) Naming Convention

| Konteks              | Format              | Contoh                            |
|-----------------------|----------------------|-----------------------------------|
| Folder / Module Name  | StudlyCase           | `ProjectManagement`, `Client`     |
| moduleKey (DB & URL)  | kebab-case           | `project-management`, `client`    |
| Route name prefix     | pendek, dot.notation | `pm.*`, `cl.*`                    |
| PHP Class / File      | StudlyCase           | `EnsureModuleAccess`              |
| PHP variable / method | camelCase            | `$moduleKey`, `getMenusByGroup()` |
| Constant              | SCREAMING_SNAKE      | `MAX_RETRIES`                     |
| DB table              | snake_case plural    | `modules`, `module_menus`         |
| DB column             | snake_case           | `entry_route`, `is_active`        |
| FK                    | `{singular}_id`      | `module_id`                       |

---

## 3) Module Structure (wajib)

Semua modul fitur harus berada di `Modules/<ModuleName>/`:

```
Modules/<ModuleName>/
├── app/Http/Controllers/       # <Prefix><Feature>Controller.php
├── app/Models/
├── app/Providers/
├── config/
├── database/migrations/
├── database/seeders/
├── resources/views/
├── Routes/web.php
├── tests/
├── module.json
├── composer.json
├── package.json
└── vite.config.js
```

---

## 4) Routing Convention

**Global routes** (`routes/web.php`):
- `GET /dashboard-modules` → `modules.dashboard` → middleware: `auth`
- `GET /m/{moduleKey}` → `ModuleEntryController@enter` → middleware: `auth`, `EnsureModuleAccess`

**Module routes** (`Modules/<ModuleName>/Routes/web.php`):
- Prefix: `/m/{moduleKey}`
- Middleware: `auth`, `EnsureModuleAccess`
- Semua route wajib `->defaults('moduleKey', '<moduleKey>')`
- Route name: `<prefix>.<feature>` (contoh: `pm.dashboard`)

---

## 5) Permission Template (wajib per modul)

```
access {moduleKey}          → Akses masuk area modul
{moduleKey}.view            → Lihat data
{moduleKey}.create          → Buat data baru
{moduleKey}.edit            → Edit data
{moduleKey}.delete          → Hapus data
```

---

## 6) Middleware EnsureModuleAccess

1. Module exists → jika tidak: **404**
2. Module `is_active = 1` → jika tidak: **403**
3. User punya permission `access {moduleKey}` → jika tidak: **403**

---

## 7) Database

- Migrations di root `database/migrations/`
- Tabel inti: `modules`, `module_menus`, `module_forms`
- Seeder wajib: roles `admin` & `user`, default admin `admin@company.test` / `password`

---

## 8) Sidebar / Module Layout

- Layout: `resources/views/layouts/module.blade.php`
- Sidebar load menus dari `module_menus` per moduleKey aktif
- Menu tampil jika `is_active=1` DAN (`permission_name` null ATAU user has permission)
- Group menus by kolom `group` — group `Admin` hanya muncul jika user punya `create|edit|delete`
- Wajib ada tombol "Back to Modules Dashboard"

---

## 9) API Response Standard (JSON)

```json
{ "success": true, "message": "...", "data": ..., "meta": { "request_id": "...", "timestamp": "..." } }
```

- Validation error: `422` + `errors` object
- General error: `4xx/5xx` + `error.code`

---

## 10) Aturan untuk AI Assistant

- **SELALU** baca `.ai/standards.md` sebelum membuat modul/fitur baru
- **JANGAN** buat route di luar pola `/m/{moduleKey}/...` untuk fitur modul
- **JANGAN** skip middleware `EnsureModuleAccess` pada route modul
- **SELALU** buat 5 permission standar saat membuat modul baru
- **SELALU** seed modul baru ke tabel `modules` dan `module_menus`
- **JANGAN** mengubah format API response wrapper
- **PERTAHANKAN** whitespace dan formatting file yang sudah ada — minimal diff
