# Requirements Document - Master Data Management (M02)

## Introduction

Modul Master Data Management adalah fondasi sistem ERP BLUD yang mengelola seluruh data master organisasi, keuangan, layanan, dan sumber daya. Modul ini menyediakan data referensi yang digunakan oleh semua modul lain dalam sistem, termasuk struktur organisasi, Chart of Accounts (COA) sesuai standar BLUD, katalog layanan, tarif, dan data sumber daya.

## Glossary

- **MDM_System**: Master Data Management System - sistem pengelolaan data master
- **Organization_Unit**: Unit organisasi dalam struktur BLUD (instalasi, bagian, unit kerja)
- **COA**: Chart of Accounts - bagan akun sesuai standar akuntansi BLUD
- **Funding_Source**: Sumber dana (APBN, APBD, PNBP, Hibah, dll)
- **Service_Catalog**: Katalog layanan medis dan non-medis yang disediakan BLUD
- **Tariff**: Tarif layanan yang ditetapkan sesuai regulasi BLUD
- **HR_Master**: Data master sumber daya manusia
- **Asset_Master**: Data master aset dan inventaris
- **BLUD**: Badan Layanan Umum Daerah
- **Installation**: Instalasi dalam struktur BLUD (Rawat Jalan, Rawat Inap, IGD, dll)
- **Cost_Center**: Pusat biaya yang terkait dengan unit organisasi
- **INA_CBG**: Indonesian Case Base Groups - sistem pembayaran berbasis diagnosis

## Requirements

### Requirement 1: Struktur Organisasi

**User Story:** Sebagai administrator sistem, saya ingin mengelola struktur organisasi BLUD secara hierarkis, sehingga dapat mencerminkan struktur organisasi yang sebenarnya dan mendukung pelaporan multi-level.

#### Acceptance Criteria

1. WHEN administrator membuat unit organisasi baru THEN THE MDM_System SHALL menyimpan data unit dengan kode unik, nama, tipe (instalasi/bagian/unit), parent unit, dan status aktif
2. WHEN administrator menetapkan parent unit THEN THE MDM_System SHALL memvalidasi bahwa tidak terjadi circular reference dalam hierarki
3. WHEN unit organisasi memiliki child units THEN THE MDM_System SHALL mencegah penghapusan unit tersebut
4. WHEN administrator mengubah hierarki organisasi THEN THE MDM_System SHALL memperbarui path hierarki untuk semua child units
5. THE MDM_System SHALL menyediakan visualisasi tree structure dari organisasi
6. WHEN unit organisasi di-nonaktifkan THEN THE MDM_System SHALL mencegah penggunaan unit tersebut dalam transaksi baru

### Requirement 2: Chart of Accounts (COA) BLUD

**User Story:** Sebagai kepala keuangan, saya ingin mengelola bagan akun sesuai standar akuntansi BLUD, sehingga dapat melakukan pencatatan dan pelaporan keuangan yang sesuai regulasi.

#### Acceptance Criteria

1. THE MDM_System SHALL mendukung struktur COA dengan format: Kelompok-Jenis-Objek-Rincian Objek-Sub Rincian Objek
2. WHEN administrator membuat akun baru THEN THE MDM_System SHALL memvalidasi kode akun sesuai format standar BLUD (X-XX-XX-XX-XXX)
3. WHEN akun memiliki sub-akun THEN THE MDM_System SHALL mencegah posting transaksi langsung ke akun header
4. THE MDM_System SHALL mengklasifikasikan akun berdasarkan kategori: Aset, Kewajiban, Ekuitas, Pendapatan, Beban
5. THE MDM_System SHALL mendukung akun dengan saldo normal Debit atau Kredit
6. WHEN akun digunakan dalam transaksi THEN THE MDM_System SHALL mencegah penghapusan akun tersebut
7. THE MDM_System SHALL menyediakan mapping akun untuk integrasi dengan sistem eksternal (SIMDA, SIPD)

### Requirement 3: Sumber Dana (Funding Sources)

**User Story:** Sebagai administrator keuangan, saya ingin mengelola sumber dana yang tersedia, sehingga dapat melacak penggunaan dana sesuai sumbernya dan memenuhi requirement pelaporan.

#### Acceptance Criteria

1. THE MDM_System SHALL mengelola sumber dana dengan tipe: APBN, APBD Provinsi, APBD Kabupaten/Kota, PNBP, Hibah, Pinjaman, Lain-lain
2. WHEN administrator membuat sumber dana THEN THE MDM_System SHALL menyimpan kode, nama, tipe, periode berlaku, dan status aktif
3. THE MDM_System SHALL memvalidasi bahwa kode sumber dana unik dalam sistem
4. WHEN sumber dana memiliki periode berlaku THEN THE MDM_System SHALL mencegah penggunaan di luar periode tersebut
5. THE MDM_System SHALL mendukung alokasi sumber dana per program/kegiatan
6. WHEN sumber dana di-nonaktifkan THEN THE MDM_System SHALL mencegah penggunaan dalam transaksi baru

### Requirement 4: Katalog Layanan

**User Story:** Sebagai kepala pelayanan, saya ingin mengelola katalog layanan medis dan non-medis, sehingga dapat mendokumentasikan semua layanan yang disediakan BLUD dan menjadi basis untuk penetapan tarif.

#### Acceptance Criteria

1. THE MDM_System SHALL mengelola layanan dengan kategori: Rawat Jalan, Rawat Inap, IGD, Penunjang Medis, Tindakan, Operasi, Persalinan, Administrasi
2. WHEN administrator membuat layanan baru THEN THE MDM_System SHALL menyimpan kode layanan, nama, kategori, unit penyedia, deskripsi, dan status aktif
3. THE MDM_System SHALL memvalidasi bahwa kode layanan unik dalam sistem
4. WHEN layanan terkait dengan INA-CBG THEN THE MDM_System SHALL menyimpan mapping kode INA-CBG
5. THE MDM_System SHALL mendukung pengelompokan layanan dalam paket layanan
6. WHEN layanan memiliki komponen biaya THEN THE MDM_System SHALL menyimpan breakdown komponen (jasa medis, jasa sarana, BMHP, obat)
7. THE MDM_System SHALL mendukung layanan dengan durasi standar (untuk scheduling)

### Requirement 5: Tarif Layanan

**User Story:** Sebagai administrator tarif, saya ingin mengelola tarif layanan dengan versioning, sehingga dapat melacak perubahan tarif dan menerapkan tarif yang berlaku sesuai periode.

#### Acceptance Criteria

1. WHEN administrator menetapkan tarif THEN THE MDM_System SHALL menyimpan layanan, kelas layanan, tarif, periode berlaku, dan status
2. THE MDM_System SHALL mendukung kelas layanan: VIP, Kelas I, Kelas II, Kelas III, Umum
3. THE MDM_System SHALL memvalidasi bahwa tidak ada overlap periode berlaku untuk tarif layanan dan kelas yang sama
4. WHEN tarif baru dibuat untuk layanan yang sudah ada THEN THE MDM_System SHALL membuat versi baru dengan periode berlaku yang berbeda
5. THE MDM_System SHALL menyimpan breakdown tarif: jasa medis, jasa sarana, BMHP, obat, administrasi
6. WHEN sistem billing membutuhkan tarif THEN THE MDM_System SHALL mengembalikan tarif yang berlaku pada tanggal transaksi
7. THE MDM_System SHALL mendukung tarif khusus untuk penjamin tertentu (BPJS, Asuransi, Perusahaan)

### Requirement 6: Data Master SDM

**User Story:** Sebagai kepala kepegawaian, saya ingin mengelola data master pegawai, sehingga dapat digunakan untuk perhitungan biaya SDM, penjadwalan, dan analisis produktivitas.

#### Acceptance Criteria

1. THE MDM_System SHALL mengelola data pegawai: NIP, nama, jabatan, unit kerja, status kepegawaian, golongan, gaji pokok
2. WHEN administrator membuat data pegawai THEN THE MDM_System SHALL memvalidasi NIP unik dalam sistem
3. THE MDM_System SHALL mengklasifikasikan pegawai berdasarkan kategori: Medis (Dokter, Perawat, Bidan), Penunjang Medis, Administrasi, Umum
4. THE MDM_System SHALL menyimpan kompetensi dan sertifikasi pegawai
5. WHEN pegawai memiliki jam kerja efektif THEN THE MDM_System SHALL menyimpan jam kerja per minggu untuk perhitungan cost rate
6. THE MDM_System SHALL mendukung penugasan pegawai ke multiple unit kerja dengan persentase alokasi waktu
7. WHEN pegawai di-nonaktifkan THEN THE MDM_System SHALL mencegah penugasan baru tetapi tetap menyimpan history

### Requirement 7: Data Master Aset dan Inventaris

**User Story:** Sebagai kepala aset, saya ingin mengelola data aset dan inventaris, sehingga dapat melacak aset, menghitung depresiasi, dan mengalokasikan biaya aset ke cost center.

#### Acceptance Criteria

1. THE MDM_System SHALL mengelola aset dengan kategori: Tanah, Gedung, Peralatan Medis, Peralatan Non-Medis, Kendaraan, Inventaris
2. WHEN administrator mencatat aset THEN THE MDM_System SHALL menyimpan kode aset, nama, kategori, nilai perolehan, tanggal perolehan, lokasi, kondisi, status
3. THE MDM_System SHALL menghitung nilai depresiasi berdasarkan metode: Garis Lurus, Saldo Menurun, Unit Produksi
4. WHEN aset memiliki umur ekonomis THEN THE MDM_System SHALL menghitung depresiasi bulanan otomatis
5. THE MDM_System SHALL mengalokasikan aset ke cost center atau unit organisasi
6. WHEN aset dipindahkan antar unit THEN THE MDM_System SHALL mencatat history perpindahan
7. THE MDM_System SHALL mendukung pengelompokan aset dalam kategori untuk pelaporan

### Requirement 8: Integrasi Data Master

**User Story:** Sebagai system integrator, saya ingin memastikan data master dapat diakses oleh modul lain dengan konsisten, sehingga tidak terjadi duplikasi atau inkonsistensi data.

#### Acceptance Criteria

1. WHEN modul lain membutuhkan data organisasi THEN THE MDM_System SHALL menyediakan API untuk query struktur organisasi
2. WHEN modul lain membutuhkan COA THEN THE MDM_System SHALL menyediakan API untuk query akun dengan filter kategori dan status
3. WHEN modul lain membutuhkan tarif THEN THE MDM_System SHALL menyediakan API untuk query tarif berdasarkan layanan, kelas, dan tanggal berlaku
4. THE MDM_System SHALL menyediakan event notification ketika data master berubah
5. WHEN data master digunakan oleh modul lain THEN THE MDM_System SHALL mencegah penghapusan data tersebut
6. THE MDM_System SHALL menyediakan audit trail untuk semua perubahan data master
7. THE MDM_System SHALL mendukung export/import data master dalam format Excel dan JSON

### Requirement 9: Validasi dan Konsistensi Data

**User Story:** Sebagai data administrator, saya ingin sistem memvalidasi konsistensi data master, sehingga dapat mencegah error dalam proses bisnis downstream.

#### Acceptance Criteria

1. WHEN data master dibuat atau diubah THEN THE MDM_System SHALL memvalidasi mandatory fields sesuai aturan bisnis
2. THE MDM_System SHALL memvalidasi format kode sesuai standar (COA, kode layanan, NIP, kode aset)
3. WHEN terdapat referential integrity THEN THE MDM_System SHALL memvalidasi foreign key relationships
4. THE MDM_System SHALL mencegah duplikasi data berdasarkan unique constraints
5. WHEN data master memiliki periode berlaku THEN THE MDM_System SHALL memvalidasi tidak ada overlap periode
6. THE MDM_System SHALL menyediakan laporan data master yang tidak konsisten atau incomplete
7. WHEN validasi gagal THEN THE MDM_System SHALL menampilkan pesan error yang jelas dan actionable

### Requirement 10: Keamanan dan Akses Data Master

**User Story:** Sebagai security administrator, saya ingin mengontrol akses ke data master berdasarkan role, sehingga hanya user yang berwenang dapat mengubah data kritis.

#### Acceptance Criteria

1. THE MDM_System SHALL menerapkan permission: access master-data-management, master-data-management.view, master-data-management.create, master-data-management.edit, master-data-management.delete
2. WHEN user mengakses data master THEN THE MDM_System SHALL memvalidasi permission sesuai jenis data (COA, Tarif, SDM, Aset)
3. THE MDM_System SHALL mencatat audit log untuk semua operasi create, update, delete dengan user, timestamp, dan perubahan data
4. WHEN data master kritis diubah THEN THE MDM_System SHALL memerlukan approval dari supervisor
5. THE MDM_System SHALL mendukung role-based access untuk data master per kategori
6. WHEN user tidak memiliki permission THEN THE MDM_System SHALL menampilkan error 403 Forbidden
7. THE MDM_System SHALL menyediakan laporan audit trail data master untuk compliance
