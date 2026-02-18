---
description: Setup awal project BackOffice BLUD dari nol (clone → serve)
---

# Setup Project

## Prasyarat
- PHP >= 8.2, Composer, Node.js >= 18, npm

## Langkah-langkah

// turbo-all

1. Copy environment file
```bash
copy .env.example .env
```

2. Install PHP dependencies
```bash
composer install
```

3. Generate application key
```bash
php artisan key:generate
```

4. Pastikan file SQLite ada
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

5. Jalankan migration + seeder
```bash
php artisan migrate --seed
```

6. Install npm dependencies
```bash
npm install
```

7. Build frontend assets
```bash
npm run build
```

8. Jalankan dev server (opsional, lihat workflow `run-dev`)
```bash
composer dev
```

## Verifikasi
- Buka `http://localhost:8000` → harus redirect ke login
- Login `admin@company.test` / `password` → harus masuk ke `/dashboard-modules`
