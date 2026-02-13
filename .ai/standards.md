# Modulify Standards

Dokumen ini adalah **single source of truth** untuk standar project Modulify. Semua perubahan kode harus mengikuti dokumen ini.

## 0) Prinsip

- **Monolith Laravel + Modular Feature** via `nwidart/laravel-modules`.
- **User area**: Blade + Tailwind (Breeze).
- **Admin panel**: Filament v3 di `/admin`, hanya role `admin`.
- **RBAC**: `spatie/laravel-permission`.
- **Module registry**: tabel `modules`, `module_menus`, `module_forms`.
- Semua modul fitur harus berada di `Modules/<ModuleName>/...`.

---

## 1) Naming Convention

### 1.1 Module naming

- **Folder/ModuleName (nwidart):** StudlyCase  
  Contoh: `ProjectManagement`, `Client`, `PatientRecords`
- **moduleKey (DB + URL):** kebab-case  
  Contoh: `project-management`, `client`
- **Route name prefix:** pendek & unik per modul  
  Contoh: `pm.*` untuk ProjectManagement

### 1.2 PHP naming

- Class / file / namespace: StudlyCase  
  Contoh: `EnsureModuleAccess`, `ModulesDashboardController`
- Variable/method: camelCase  
  Contoh: `$moduleKey`, `getMenusByGroup()`
- Constants: SCREAMING_SNAKE_CASE

### 1.3 DB naming

- Table: snake_case plural  
  `modules`, `module_menus`, `module_forms`
- Column: snake_case  
  `entry_route`, `is_active`, `permission_name`, `schema_json`
- FK: `{singular}_id`  
  `module_id`

---

## 2) URL, Routes, Middleware

### 2.1 Global routes (wajib)

- `GET /dashboard-modules`
  - name: `modules.dashboard`
  - controller: `ModulesDashboardController@index`
  - middleware: `auth`
- `GET /m/{moduleKey}`
  - controller: `ModuleEntryController@enter` (redirect ke `modules.entry_route`)
  - middleware: `auth`, `EnsureModuleAccess`

### 2.2 Module area (wajib)

- Semua route modul harus berada di: `Modules/<ModuleName>/Routes/web.php`
- Prefix publik module: `/m/<moduleKey>`
- Semua route modul wajib middleware: `auth`, `EnsureModuleAccess`

### 2.3 EnsureModuleAccess (wajib)

- Ambil `moduleKey` dari route parameter `moduleKey`
- Validasi:
  1. module ada → jika tidak: **404**
  2. module `is_active=1` → jika tidak: **403**
  3. user punya permission `access {moduleKey}` → jika tidak: **403**

---

## 3) Permission Standard (wajib)

Untuk setiap modul dengan key `{moduleKey}` harus ada permissions:

- `access {moduleKey}`
- `{moduleKey}.view`
- `{moduleKey}.create`
- `{moduleKey}.edit`
- `{moduleKey}.delete`

Aturan akses:

- Semua route `/m/{moduleKey}/...` → `EnsureModuleAccess` (cek `access {moduleKey}`)
- View pages → butuh `{moduleKey}.view`
- Create page → butuh `{moduleKey}.create`
- Edit page → butuh `{moduleKey}.edit`
- Delete actions → butuh `{moduleKey}.delete`

---

## 4) Module Dashboard & Module Layout

### 4.1 Modules Dashboard (wajib)

- Setelah login redirect ke `/dashboard-modules`.
- Tampilkan grid card modul yang:
  - module aktif (`is_active=1`)
  - user punya permission `access {moduleKey}`
- Klik modul → masuk `/m/{moduleKey}` (ModuleEntryController)

### 4.2 Module Area layout (wajib)

Layout file: `resources/views/layouts/module.blade.php`
Komponen wajib:

- Sidebar (menus dari DB)
- Topbar
- Tombol “Back to Modules Dashboard” → `route('modules.dashboard')`

Sidebar rules:

- Load module + menus dari DB untuk moduleKey aktif.
- Menu tampil jika `is_active=1` dan:
  - `permission_name` null, atau user bisa `permission_name`.
- Group menus by `group` (contoh: `Main`, `Admin`).
- Group `Admin` hanya muncul jika user punya salah satu:
  - `{moduleKey}.create` OR `{moduleKey}.edit` OR `{moduleKey}.delete`

---

## 5) Database Schema (wajib)

Migrations boleh di root (`database/migrations`).

### 5.1 modules

Kolom:

- `id`
- `key` (unique)
- `name`
- `description` nullable
- `icon` nullable
- `entry_route` (string)
- `sort` int default 0
- `is_active` bool default true
- timestamps

### 5.2 module_menus

Kolom:

- `id`
- `module_id` FK -> modules.id (cascade delete)
- `label`
- `route_name`
- `icon` nullable
- `sort` int default 0
- `permission_name` nullable
- `group` nullable
- `is_active` bool default true
- timestamps

### 5.3 module_forms

Kolom:

- `id`
- `module_id` FK -> modules.id (cascade delete)
- `key` string
- `name` string
- `schema_json` json
- `is_active` bool default true
- timestamps

---

## 6) Seed Data Standard (wajib)

Seeder harus jalan via `php artisan migrate --seed`.

Minimum seed:

- roles: `admin`, `user`
- default admin user:
  - email: `admin@company.test`
  - password: `password`
  - role: `admin`

Admin role:

- mendapatkan semua permissions (minimal untuk module example).

User role:

- minimal dapat:
  - `access <moduleKey>`
  - `<moduleKey>.view`

---

## 7) Example module: ProjectManagement (format baku)

### 7.1 Module metadata

- ModuleName folder: `ProjectManagement`
- moduleKey: `project-management`
- entry_route: `pm.dashboard`

### 7.2 Module routes (wajib)

Prefix: `/m/project-management`
Middleware group: `auth`, `EnsureModuleAccess`

Routes:

- `GET /dashboard` → `pm.dashboard` → can: `project-management.view`
- `GET /projects` → `pm.projects.index` → can: `project-management.view`
- `GET /projects/create` → `pm.projects.create` → can: `project-management.create`
- `GET /settings` → `pm.settings` → can: `project-management.edit`

### 7.3 Module controllers

- `Modules/ProjectManagement/App/Http/Controllers/PmDashboardController.php`
- `Modules/ProjectManagement/App/Http/Controllers/PmProjectsController.php`
- `Modules/ProjectManagement/App/Http/Controllers/PmSettingsController.php`

### 7.4 Module views

- `Modules/ProjectManagement/Resources/views/dashboard.blade.php`
- `Modules/ProjectManagement/Resources/views/projects/index.blade.php`
- `Modules/ProjectManagement/Resources/views/projects/create.blade.php`
- `Modules/ProjectManagement/Resources/views/settings.blade.php`

---

## 8) Dynamic Form Standard (wajib untuk example)

- `module_forms.schema_json` harus memuat minimal:
  - wizard steps >= 2
  - conditional field (visibleWhen)
  - repeater items (itemSchema)
- Halaman `pm.projects.create` wajib merender schema tersebut (Livewire recommended).

Schema shape yang direkomendasikan:

- root: `type=wizard`, `steps=[...]`
- field:
  - `type`, `name`, `label`, `rules` (optional), `options` (optional)
- conditional:
  - `visibleWhen: { field, operator, value }`
- repeater:
  - `{ type:"repeater", name:"...", itemSchema:[...] }`

---

## 9) API Response Standard (wajib untuk JSON endpoints)

Semua endpoint yang merespon JSON (khususnya `/api/*` atau request dengan Accept: application/json) harus return format:

### 9.1 Success (single)

HTTP 200/201
{
"success": true,
"message": "OK",
"data": { ... },
"meta": { "request_id": "...", "timestamp": "..." }
}

### 9.2 Success (list)

HTTP 200
{
"success": true,
"message": "OK",
"data": [ ... ],
"meta": {
"pagination": { "page": 1, "per_page": 15, "total": 0, "last_page": 1 },
"request_id": "...",
"timestamp": "..."
}
}

### 9.3 Validation error

HTTP 422
{
"success": false,
"message": "Validation failed",
"errors": { "field": ["..."] },
"meta": { "request_id": "...", "timestamp": "..." }
}

### 9.4 General error

HTTP 4xx/5xx
{
"success": false,
"message": "<Human readable>",
"error": { "code": "<MACHINE_CODE>", "details": null },
"meta": { "request_id": "...", "timestamp": "..." }
}

Status codes:

- 200, 201, 204, 400, 401, 403, 404, 409, 422, 500

Implementasi rekomendasi:

- Helper/trait `ApiResponse` untuk builder response
- Exception mapping untuk JSON requests:
  - ValidationException -> 422 + errors
  - Authorization -> 403
  - Authentication -> 401
  - ModelNotFound/404 -> 404
  - default -> 500
