# Modulify Agent Instructions (Standards-First)

WAJIB:

1. Baca dan patuhi `.ai/standards.md` sebagai single source of truth.
2. Jika task/prompt bertentangan dengan `.ai/standards.md`, PRIORITASKAN task, tapi tulis "DEVIATIONS" di akhir dengan alasan.
3. Gunakan Laravel Boost (MCP) untuk cek docs/versi paket bila ada keraguan (Breeze/Filament/Spatie/nwidart).
4. Output kerja wajib:
   - TODO checklist (awal)
   - Progress updates (✅/⏳/❌)
   - Final summary + file penting + command verifikasi
5. Untuk endpoint JSON: patuhi API response contract di `.ai/standards.md`.
6. Sebelum commit/PR: jalankan verifikasi minimal:
   - `php artisan migrate --seed`
   - `php artisan test` (jika ada)
   - `npm run build` (jika ada)
   - (jika tersedia) `php artisan modulify:validate`

DILARANG:

- Mengubah naming convention, permission template, route pattern, schema DB modul global tanpa update `.ai/standards.md`.
