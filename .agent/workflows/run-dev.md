---
description: Cara menjalankan dev server untuk development lokal
---

# Jalankan Dev Server

## Cara Cepat (recommended)

// turbo
```bash
composer dev
```

Ini menjalankan 4 proses secara bersamaan via `concurrently`:
- `php artisan serve` — Laravel server (port 8000)
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log tail (real-time)
- `npm run dev` — Vite dev server (HMR)

## Cara Manual (jika `composer dev` bermasalah)

Buka 2 terminal terpisah:

**Terminal 1 — Laravel server:**
```bash
php artisan serve
```

**Terminal 2 — Vite dev server:**
```bash
npm run dev
```

## Akses

- App: `http://localhost:8000`
- Login: `admin@company.test` / `password`
- Admin panel: `http://localhost:8000/admin`
- Modules dashboard: `http://localhost:8000/dashboard-modules`
