---
description: Cara menambah menu sidebar ke modul yang sudah ada
---

# Menambah Menu Sidebar

> Referensi: `.ai/standards.md` bagian 4.2 & 5.2

## Input yang Dibutuhkan

| Parameter         | Contoh                         |
|-------------------|--------------------------------|
| `moduleKey`       | `project-management`           |
| `label`           | `Projects`                     |
| `route_name`      | `pm.projects.index`            |
| `icon`            | `heroicon-o-folder`            |
| `group`           | `Main` atau `Admin`            |
| `permission_name` | `project-management.view` / null |

## Langkah-langkah

### 1. Pastikan route sudah ada

```bash
php artisan route:list --name=<route_name>
```

### 2. Insert ke `module_menus`

Via seeder atau tinker:

```php
$module = \App\Models\Module::where('key', '<moduleKey>')->first();

\App\Models\ModuleMenu::create([
    'module_id'       => $module->id,
    'label'           => '<label>',
    'route_name'      => '<route_name>',
    'icon'            => '<icon>',
    'sort'            => 10,              // sesuaikan urutan
    'permission_name' => '<permission>',  // null = public ke semua user modul
    'group'           => '<group>',       // 'Main' atau 'Admin'
    'is_active'       => true,
]);
```

### 3. Aturan group

- `Main` → tampil untuk semua user yang punya `access {moduleKey}`
- `Admin` → hanya tampil jika user punya `{moduleKey}.create` OR `.edit` OR `.delete`
- `permission_name = null` → menu tampil selama user punya akses modul
- `permission_name = '{moduleKey}.edit'` → menu hanya tampil jika user punya permission tersebut

## Verifikasi

- Login → masuk ke `/m/<moduleKey>` → sidebar harus menampilkan menu baru
- Cek visibility berdasarkan role (admin vs user biasa)
