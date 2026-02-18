---
description: Cara membuat permission set standar dan assign ke role
---

# Seed Permissions untuk Modul

> Referensi: `.ai/standards.md` bagian 3

## Template 5 Permission per Modul

Untuk setiap modul dengan key `{moduleKey}`:

```
access {moduleKey}
{moduleKey}.view
{moduleKey}.create
{moduleKey}.edit
{moduleKey}.delete
```

## Langkah-langkah

### 1. Buat permission via seeder/tinker

```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
```

### 2. Assign ke role `admin` (semua permission)

```php
$admin = Role::findByName('admin');
$admin->givePermissionTo($permissions);
```

### 3. Assign ke role `user` (hanya akses + view)

```php
$user = Role::findByName('user');
$user->givePermissionTo([
    "access {$key}",
    "{$key}.view",
]);
```

### 4. (Opsional) Buat role khusus modul

```php
$managerRole = Role::firstOrCreate(['name' => "{$key}-manager"]);
$managerRole->givePermissionTo($permissions);
```

## Verifikasi

```bash
php artisan permission:show
```

- Role `admin` harus punya semua 5 permission
- Role `user` harus punya `access {moduleKey}` dan `{moduleKey}.view`
