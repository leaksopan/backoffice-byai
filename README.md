# Modulify - Modular Laravel Starter (Modules Dashboard + RBAC + Liquid Glass)

Modulify is a modular Laravel starter kit designed for teams that need fast feature delivery with clean module boundaries, role-based access control, and centralized app settings. It ships with a module dashboard, admin tooling, and a reusable Liquid Glass UI layer so new modules can be added consistently.

## Features

- Modular architecture via `nwidart/laravel-modules` (`1 module = 1 folder`)
- RBAC per module (`access` + CRUD permissions) via `spatie/laravel-permission`
- Modules Dashboard that lists only modules the current user can access
- Admin Center for users, roles, permissions, and module access management
- Settings module for app branding (`name`, `logo`, `favicon`)
- Liquid Glass theme with global CSS utilities

## Tech Stack

- Laravel
- Laravel Breeze (Blade)
- Tailwind CSS
- `nwidart/laravel-modules`
- `spatie/laravel-permission`

## Quick Start (Local)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Default Credentials

```text
Email: admin@company.test
Password: password
```

## Important URLs

- `/login`
- `/dashboard-modules`
- `/m/admin-center/dashboard`
- `/m/settings/dashboard`
- `/m/example-modules/dashboard`

## Folder Structure (High-level)

```text
Modules/<ModuleName>/...
app/Http/Middleware/EnsureModuleAccess
resources/views/layouts/module.blade.php
database/seeders
```

## Adding a New Module

1. Run `php artisan module:make <Name>`.
2. Add routes with prefix `/m/<module-key>`.
3. Add permissions: `access`, `view`, `create`, `edit`, `delete`.
4. Seed `modules` table record (`name`, `key`, `entry_route`, `sort_order`, `is_active`).
5. Seed `module_menus` entries.
6. Assign permissions to roles.
7. Test access control and sidebar visibility.

## Modules List Management (DB)

- `modules` table supports `sort_order` and `is_active` for ordering and hide/unhide behavior.
- `module_menus` controls sidebar navigation items per module.

## Theming Notes

- Global CSS lives in `resources/css/app.css`.
- Use `glass-*` utility classes such as `glass-card`, `glass-topbar`, `glass-sidebar`, and `glass-btn`.

## Verify Commands

```bash
php artisan module:list
php artisan migrate --seed
npm run build
```

## License / Credits

MIT licensed. Built on Laravel with community packages from `nwidart/laravel-modules` and `spatie/laravel-permission`.
