# Modulify Modules Guide

## Architecture Overview

Modulify uses a monolith Laravel app with feature modules powered by `nwidart/laravel-modules`.

- Core shell lives in `app/` and `resources/views/layouts/`.
- Feature code lives in `Modules/{ModuleName}`.
- Access control uses `spatie/laravel-permission`.
- Module registry and navigation come from database tables:
  - `modules`
  - `module_menus`
  - `module_forms` (optional dynamic form schema)

Route entry pattern:

- Dashboard: `/dashboard-modules`
- Module entry: `/m/{moduleKey}` (redirects to module `entry_route`)
- Module pages: `/m/{moduleKey}/...`

## Add A Module Record

1. Add module routes inside module `routes/web.php`.
2. Make sure entry route name exists (example: `example.dashboard`).
3. Insert module record into `modules` with:
   - `key` (kebab-case, unique)
   - `name`
   - `entry_route`
   - `sort_order`
   - `is_active`
4. Seed standard permissions for module key `{k}`:
   - `access {k}`
   - `{k}.view`
   - `{k}.create`
   - `{k}.edit`
   - `{k}.delete`

## Add `module_menus` Records

Insert one or more rows to `module_menus`:

- `module_key`: module key (example `example-modules`)
- `section`: `MAIN` or `ADMIN`
- `label`: menu label
- `route_name`: named route target (nullable)
- `url`: direct URL target (nullable)
- `icon`: icon key (example `heroicon-o-home`)
- `permission_name`: permission to check before showing item (nullable)
- `sort_order`: ordering inside section
- `is_active`: show/hide toggle

Rendering behavior:

- Menus are loaded by current `module_key`.
- Menu items are sorted by `section`, then `sort_order`.
- If `permission_name` exists, unauthorized users will not see the item.

## Permission Standard

For every module key `{k}`:

- Gate module entry with `EnsureModuleAccess` (`access {k}`).
- Use route middleware for page actions:
  - View routes: `can:{k}.view`
  - Create routes: `can:{k}.create`
  - Edit routes: `can:{k}.edit`
  - Delete routes/actions: `can:{k}.delete`

## Common Mistakes

- Module key mismatch between URL, permissions, and DB records.
- `entry_route` points to a non-existing route name.
- Missing `access {k}` permission causes 403 for all module routes.
- Menu `route_name` missing or typo causes broken links.
- Forgetting to seed permissions or assign them to roles.
- Not running `php artisan optimize:clear` after route/layout updates.
