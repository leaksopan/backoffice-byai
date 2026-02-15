# Modulify

Production-ready modular platform built on Laravel with database-driven module registry, RBAC, and dynamic module navigation.

## Features

- Laravel 12 + Breeze (Blade) authentication.
- Modular architecture with `nwidart/laravel-modules`.
- RBAC with `spatie/laravel-permission`.
- Post-login launcher dashboard at `/dashboard-modules`.
- DB-driven modules registry (`modules`) with ordering and hide/unhide.
- DB-driven sidebar menus (`module_menus`) per module.
- Admin Center for:
  - Users CRUD
  - Roles CRUD
  - Permissions CRUD
  - Role assignment to user
  - Permission assignment to role
  - Module Access Matrix
  - Modules Management (sort + hide/unhide)
- Liquid Glass UI shell with:
  - Responsive sidebar
  - Desktop collapsed mode with per-item tooltip
  - Theme toggle (light/dark)
- Settings module for global app branding:
  - App name
  - Tagline
  - Logo light/dark
  - Favicon
- ExampleModules module as in-app developer guide.

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Breeze (Blade + Alpine)
- Tailwind CSS v3
- Livewire 3
- nwidart/laravel-modules
- spatie/laravel-permission
- Filament v3 (admin panel support)

## Quickstart

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Login with seeded admin:

- Email: `admin@company.test`
- Password: `password`

## Core URLs

- `/` -> redirect to `/login`
- `/login`
- `/dashboard-modules`
- `/profile`
- `/m/admin-center/dashboard`
- `/m/settings/dashboard`
- `/m/example-modules/dashboard`

## Module Checklist

Core seeded modules:

- `admin-center`
- `settings`
- `example-modules`

For each module key `{k}`, permissions are standardized:

- `access {k}`
- `{k}.view`
- `{k}.create`
- `{k}.edit`
- `{k}.delete`

## Database Management

Primary tables:

- `modules`: module registry (`entry_route`, `sort_order`, `is_active`).
- `module_menus`: module sidebar items (`module_key`, `section`, `permission_name`, `sort_order`).
- `app_settings`: app key-value settings with cache-backed helper.

Seeder behavior:

- Creates roles: `super-admin`, `admin`, `user`.
- Creates default admin account.
- Seeds core modules + module menus.
- Seeds module permissions and role assignments.

## Theming

Global theme uses Liquid Glass utility classes in `resources/css/app.css`.

- Background: `bg-app-surface`
- Panels: `glass-panel`
- Soft controls: `glass-soft`
- Chips/badges: `glass-chip`

Branding values are read from helper:

- `setting('app.name', config('app.name'))`
- `setting('app.tagline')`
- `setting('branding.logo_light')`
- `setting('branding.logo_dark')`
- `setting('branding.favicon')`

## Module Generator

Generate a ready-to-use module (dashboard + sample Item CRUD + seeder + tests):

```bash
php artisan modulify:make "Inventory"
php artisan modulify:make "Inventory" --with-crud --entity=Item --force
php artisan test
```

## Verify Commands

```bash
php artisan module:list
php artisan migrate --seed
php artisan route:list --path='m/'
npm run build
php artisan optimize:clear
```

## Additional Guide

See full module implementation guide:

- `docs/Modulify-Modules-Guide.md`
