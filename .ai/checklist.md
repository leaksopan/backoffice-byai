# Modulify Compliance Checklist

Gunakan checklist ini sebelum merge/commit besar.

## A) Repo & Setup

- [ ] Laravel latest stable terpasang di root project
- [ ] `.env` menggunakan SQLite dev (`database/database.sqlite`)
- [ ] `php artisan key:generate` sudah dijalankan (untuk dev)

## B) Packages

- [ ] `laravel/breeze` (blade) terpasang
- [ ] `spatie/laravel-permission` terpasang + migrations jalan
- [ ] `filament/filament` v3 terpasang + panel `/admin`
- [ ] `nwidart/laravel-modules` terpasang + ModulesServiceProvider aktif

## C) DB Schema Modul Global

- [ ] migration `modules` dibuat dan jalan
- [ ] migration `module_menus` dibuat dan jalan
- [ ] migration `module_forms` dibuat dan jalan

## D) Auth Redirect

- [ ] Login redirect ke `route('modules.dashboard')` (`/dashboard-modules`)
- [ ] Breeze scaffolding tidak rusak

## E) Middleware & Routing

- [ ] Middleware `EnsureModuleAccess` dibuat sesuai aturan (404/403)
- [ ] Route `/m/{moduleKey}` memakai `auth` + `EnsureModuleAccess`
- [ ] Semua route modul memakai `auth` + `EnsureModuleAccess`

## F) Modules Dashboard

- [ ] `/dashboard-modules` menampilkan modul aktif sesuai permission `access {moduleKey}`
- [ ] Card modul punya icon, name, description, link ke `/m/{moduleKey}`

## G) Module Area Layout & Sidebar

- [ ] Layout `resources/views/layouts/module.blade.php` ada
- [ ] Sidebar ambil menus dari DB dan group by `group`
- [ ] Menu tampil hanya jika `is_active=1` dan permission terpenuhi
- [ ] Group `Admin` hanya muncul jika user punya create/edit/delete modul

## H) Example Module: ProjectManagement

- [ ] Folder `Modules/ProjectManagement` ada
- [ ] Routes di `Modules/ProjectManagement/Routes/web.php` sesuai standar
- [ ] Controllers & views sesuai path standar
- [ ] Permission `project-management.*` + `access project-management` dibuat

## I) Dynamic Form Example

- [ ] Ada 1 record module_forms untuk `project-management`
- [ ] Schema JSON mengandung wizard steps >= 2
- [ ] Ada conditional field (visibleWhen)
- [ ] Ada repeater items (itemSchema)
- [ ] Halaman `pm.projects.create` merender schema tersebut

## J) Filament Admin

- [ ] Panel `/admin` hanya bisa diakses role `admin`
- [ ] Resources minimal: Users, Modules, ModuleMenus, ModuleForms
- [ ] Assign roles untuk user berjalan

## K) API Response Standard (JSON)

- [ ] Semua endpoint JSON memakai wrapper `success/message/data/meta`
- [ ] Validation errors pakai HTTP 422 + `errors`
- [ ] Exceptions untuk JSON requests di-map ke format standar

## L) Commands Verifikasi (disarankan)

- [ ] `composer install`
- [ ] `php artisan migrate --seed`
- [ ] `npm install`
- [ ] `npm run build` (atau `npm run dev` sesuai package.json)
- [ ] (opsional) `php artisan test`
