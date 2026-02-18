# Deploy Laravel ke Vercel

Proyek ini sudah dikonfigurasi agar bisa di-deploy di Vercel menggunakan [PHP Runtime untuk Vercel](https://github.com/vercel-community/php).

## Persyaratan

- Akun [Vercel](https://vercel.com)
- Vercel CLI (opsional): `npm i -g vercel`

## Langkah deploy

### 1. Push ke GitHub/GitLab/Bitbucket

Pastikan kode sudah di-push ke repo yang terhubung Vercel.

### 2. Import project di Vercel

- Buka [vercel.com/new](https://vercel.com/new)
- Import repo ini
- **Framework Preset:** Other (jangan pilih Laravel — tidak ada preset)
- **Root Directory:** kosongkan
- **Build Command** dan **Install Command** sudah diatur di `vercel.json`

### 3. Environment variables

Tambahkan di **Vercel → Project → Settings → Environment Variables**:

**Wajib:**

| Variable     | Contoh / Keterangan |
|-------------|----------------------|
| `APP_KEY`   | Generate: `php artisan key:generate --show` |
| `APP_URL`   | `https://your-app.vercel.app` (ganti dengan URL Vercel Anda) |
| `APP_ENV`   | `production` |
| `APP_DEBUG` | `false` |

**Agar Laravel jalan baik di serverless (cache/session):**

| Variable              | Nilai |
|-----------------------|--------|
| `APP_CONFIG_CACHE`    | `/tmp/config.php` |
| `APP_EVENTS_CACHE`    | `/tmp/events.php` |
| `APP_PACKAGES_CACHE`  | `/tmp/packages.php` |
| `APP_ROUTES_CACHE`    | `/tmp/routes.php` |
| `APP_SERVICES_CACHE`  | `/tmp/services.php` |
| `VIEW_COMPILED_PATH`  | `/tmp` |
| `LOG_CHANNEL`         | `stderr` |
| `SESSION_DRIVER`      | `cookie` |

**Database (sesuaikan dengan provider):**

- Untuk **Neon (Postgres):**  
  `DB_CONNECTION=pgsql` dan `DATABASE_URL` dari Neon (tanpa pooled connection).
- Untuk **Turso (SQLite):** pakai [Turso Laravel driver](https://github.com/tursodatabase/turso-driver-laravel).
- Untuk **MySQL/Postgres lain:** set `DB_*` seperti di `.env` lokal.

### 4. Deploy

- **Via dashboard:** setiap push ke branch yang terhubung akan auto-deploy.
- **Via CLI:** dari root project jalankan `vercel` (atau `vercel --prod` untuk production).

## Error: Command "composer install ..." exited with 127

**Penyebab:** Di lingkungan build Vercel hanya tersedia Node.js; perintah `composer` (PHP) tidak ada, jadi exit code 127 = "command not found".

**Solusi:** Install command di `vercel.json` sudah diset hanya `npm ci`. Agar Laravel jalan, dependency PHP (**vendor**) harus ikut ter-deploy. Caranya:

1. **Commit folder `vendor`** (sekali saja, atau setiap kali ubah `composer.json`):
   ```bash
   composer install --no-dev --optimize-autoloader
   git add -f vendor
   git commit -m "chore: add vendor for Vercel deploy"
   git push
   ```
2. File **`.vercelignore`** jangan mengabaikan `vendor` (kalau `vendor` sudah di-commit, folder itu akan ikut di-upload Vercel).

Kalau tidak mau commit `vendor`, deploy Laravel di platform yang mendukung PHP build (Railway, Render, Laravel Forge, Ploi).

## Catatan

- **Trust proxies** sudah diaktifkan di `bootstrap/app.php` agar Laravel cocok dengan proxy Vercel (AWS).
- **Region** di `vercel.json` diset `sin1` (Singapore). Bisa diubah di `vercel.json` → `regions` (mis. `fra1` untuk Frankfurt).
- Untuk production serius (DB besar, queue, scheduler), pertimbangkan [Laravel Forge](https://forge.laravel.com/) atau [Ploi](https://ploi.io/).

## Referensi

- [Deploy Laravel 11 on Vercel (2024)](https://edjohnsonwilliams.co.uk/blog/2024-06-04-deploy-laravel-11-for-free-on-vercel-in-2024/)
- [Vercel PHP Runtime](https://github.com/vercel-community/php)
