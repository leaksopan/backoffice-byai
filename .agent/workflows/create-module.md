---
description: Cara membuat modul baru sesuai standar Modulify
---

# Membuat Modul Baru

> Referensi lengkap: `.ai/standards.md` bagian 1, 2, 3, 5, 7

## Input yang Dibutuhkan

| Parameter          | Contoh                 | Format       |
|--------------------|------------------------|--------------|
| `ModuleName`       | `ProjectManagement`    | StudlyCase   |
| `moduleKey`        | `project-management`   | kebab-case   |
| `routePrefix`      | `pm`                   | pendek/unik  |

## Langkah-langkah

### 1. Generate module skeleton via nwidart

```bash
php artisan module:make <ModuleName>
```

### 2. Seed ke tabel `modules`

Buat seeder atau jalankan via tinker:

```php
\App\Models\Module::create([
    'key'         => '<moduleKey>',
    'name'        => '<Nama Tampilan>',
    'description' => '<Deskripsi singkat>',
    'icon'        => '<heroicon-name>',           // nullable
    'entry_route' => '<routePrefix>.dashboard',
    'sort'        => 0,
    'is_active'   => true,
]);
```

### 3. Buat 5 permission standar

```php
use Spatie\Permission\Models\Permission;

$key = '<moduleKey>';
$permissions = [
    "access {$key}",
    "{$key}.view",
    "{$key}.create",
    "{$key}.edit",
    "{$key}.delete",
];

foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

// Assign semua ke role admin
$admin = \Spatie\Permission\Models\Role::findByName('admin');
$admin->givePermissionTo($permissions);

// Assign view ke role user
$user = \Spatie\Permission\Models\Role::findByName('user');
$user->givePermissionTo(["access {$key}", "{$key}.view"]);
```

### 4. Buat route file (`Modules/<ModuleName>/Routes/web.php`)

```php
<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\<ModuleName>\Http\Controllers\<Prefix>DashboardController;

Route::prefix('m/<moduleKey>')
    ->middleware(['auth', EnsureModuleAccess::class])
    ->name('<routePrefix>.')
    ->group(function (): void {
        Route::get('/dashboard', [<Prefix>DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:<moduleKey>.view')
            ->defaults('moduleKey', '<moduleKey>');
    });
```

### 5. Buat controller

Path: `Modules/<ModuleName>/app/Http/Controllers/<Prefix>DashboardController.php`

```php
<?php

namespace Modules\<ModuleName>\Http\Controllers;

use Illuminate\Http\Request;

class <Prefix>DashboardController
{
    public function index(Request $request)
    {
        return view('<modulename>::dashboard');
    }
}
```

### 6. Buat view

Path: `Modules/<ModuleName>/resources/views/dashboard.blade.php`

```blade
@extends('layouts.module')

@section('title', '<Nama Modul> — Dashboard')

@section('content')
    <h1>Dashboard <Nama Modul></h1>
@endsection
```

### 7. Seed menu sidebar

```php
\App\Models\ModuleMenu::create([
    'module_id'       => $module->id,
    'label'           => 'Dashboard',
    'route_name'      => '<routePrefix>.dashboard',
    'icon'            => 'heroicon-o-home',
    'sort'            => 0,
    'permission_name' => null,             // null = semua yang punya access
    'group'           => 'Main',
    'is_active'       => true,
]);
```

## Verifikasi

- `php artisan route:list --path=m/<moduleKey>` → route muncul
- Login → `/dashboard-modules` → card modul baru muncul
- Klik card → masuk ke dashboard modul via `/m/<moduleKey>`
