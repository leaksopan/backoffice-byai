# Requirements Document - Cost Center & Responsibility Center Management (M03)

## Introduction

Modul Cost Center & Responsibility Center Management adalah sistem pengelolaan pusat biaya dan pusat pertanggungjawaban dalam struktur organisasi BLUD. Modul ini memetakan unit organisasi ke dalam cost center yang sesuai dengan prinsip Activity Based Costing (ABC), memungkinkan alokasi biaya yang akurat, dan mendukung analisis profitabilitas per unit layanan. Modul ini terintegrasi erat dengan Master Data Management (M02) untuk struktur organisasi dan akan menjadi basis untuk Resource Cost Management (M04) dan Unit Cost Engine (M07).

## Glossary

- **Cost_Center_System**: Sistem pengelolaan pusat biaya dan pusat pertanggungjawaban
- **Cost_Center**: Pusat biaya - unit organisasi yang mengakumulasi biaya untuk tujuan pelaporan dan analisis
- **Responsibility_Center**: Pusat pertanggungjawaban - unit organisasi yang memiliki tanggung jawab atas biaya, pendapatan, atau profit
- **Medical_Cost_Center**: Pusat biaya medis - unit yang memberikan layanan medis langsung (Rawat Jalan, Rawat Inap, IGD, OK)
- **Non_Medical_Cost_Center**: Pusat biaya non-medis - unit penunjang medis (Laboratorium, Radiologi, Farmasi, Gizi)
- **Administrative_Cost_Center**: Pusat biaya administratif - unit overhead (Keuangan, SDM, Umum, IT)
- **Profit_Center**: Pusat laba - unit yang bertanggung jawab atas pendapatan dan biaya
- **Cost_Allocation**: Alokasi biaya - proses mendistribusikan biaya dari satu cost center ke cost center lain
- **Allocation_Base**: Dasar alokasi - metrik yang digunakan untuk mengalokasikan biaya (jumlah pasien, luas ruangan, jam kerja)
- **Direct_Cost**: Biaya langsung - biaya yang dapat ditelusuri langsung ke cost center
- **Indirect_Cost**: Biaya tidak langsung - biaya yang harus dialokasikan ke cost center
- **Cost_Pool**: Kumpulan biaya - agregasi biaya untuk tujuan alokasi
- **Service_Line**: Lini layanan - pengelompokan layanan berdasarkan karakteristik klinis atau operasional
- **BLUD**: Badan Layanan Umum Daerah
- **ABC**: Activity Based Costing - metode perhitungan biaya berdasarkan aktivitas

## Requirements

### Requirement 1: Definisi Cost Center

**User Story:** Sebagai manajer keuangan, saya ingin mendefinisikan cost center berdasarkan struktur organisasi, sehingga dapat mengakumulasi dan melacak biaya per unit secara sistematis.

#### Acceptance Criteria

1. WHEN manajer membuat cost center baru THEN THE Cost_Center_System SHALL menyimpan kode unik, nama, tipe cost center, unit organisasi terkait, dan status aktif
2. THE Cost_Center_System SHALL mengklasifikasikan cost center berdasarkan tipe: Medical, Non-Medical, Administrative, Profit Center
3. WHEN cost center dibuat THEN THE Cost_Center_System SHALL memvalidasi bahwa unit organisasi yang dipilih belum memiliki cost center aktif
4. THE Cost_Center_System SHALL mengaitkan cost center dengan satu unit organisasi dari Master Data Management
5. WHEN cost center tipe Medical dibuat THEN THE Cost_Center_System SHALL memvalidasi bahwa unit organisasi bertipe 'installation' atau 'department'
6. THE Cost_Center_System SHALL menyimpan informasi tambahan: deskripsi, tanggal efektif, cost center manager
7. WHEN cost center di-nonaktifkan THEN THE Cost_Center_System SHALL mencegah posting biaya baru tetapi tetap menyimpan history

### Requirement 2: Hierarki Cost Center

**User Story:** Sebagai controller, saya ingin menetapkan hierarki cost center yang mencerminkan struktur pelaporan, sehingga dapat melakukan konsolidasi biaya multi-level.

#### Acceptance Criteria

1. WHEN cost center memiliki parent cost center THEN THE Cost_Center_System SHALL menyimpan relasi hierarki
2. THE Cost_Center_System SHALL memvalidasi bahwa parent cost center tidak membuat circular reference
3. WHEN hierarki cost center berubah THEN THE Cost_Center_System SHALL memperbarui hierarchy path untuk semua child cost centers
4. THE Cost_Center_System SHALL menyediakan query untuk mendapatkan semua descendant cost centers
5. WHEN cost center memiliki child cost centers THEN THE Cost_Center_System SHALL mencegah penghapusan cost center tersebut
6. THE Cost_Center_System SHALL mendukung konsolidasi biaya dari child ke parent cost center
7. THE Cost_Center_System SHALL menyediakan visualisasi tree structure dari hierarki cost center

### Requirement 3: Cost Center Classification

**User Story:** Sebagai cost accountant, saya ingin mengklasifikasikan cost center berdasarkan karakteristik operasional, sehingga dapat menerapkan metode alokasi biaya yang sesuai.

#### Acceptance Criteria

1. THE Cost_Center_System SHALL mengklasifikasikan Medical Cost Center berdasarkan service line: Rawat Jalan, Rawat Inap, IGD, Operasi, Persalinan, ICU
2. THE Cost_Center_System SHALL mengklasifikasikan Non-Medical Cost Center berdasarkan fungsi: Laboratorium, Radiologi, Farmasi, Gizi, Laundry, CSSD
3. THE Cost_Center_System SHALL mengklasifikasikan Administrative Cost Center berdasarkan fungsi: Keuangan, SDM, Umum, IT, Pemasaran, Hukum
4. WHEN cost center diklasifikasikan sebagai Profit Center THEN THE Cost_Center_System SHALL mengaktifkan tracking pendapatan dan biaya
5. THE Cost_Center_System SHALL mendukung multiple classification tags per cost center untuk analisis cross-dimensional
6. WHEN cost center classification berubah THEN THE Cost_Center_System SHALL mencatat perubahan dalam audit log
7. THE Cost_Center_System SHALL menyediakan laporan cost center berdasarkan classification

### Requirement 4: Cost Allocation Rules

**User Story:** Sebagai cost accountant, saya ingin mendefinisikan aturan alokasi biaya antar cost center, sehingga biaya overhead dapat didistribusikan secara adil dan akurat.

#### Acceptance Criteria

1. WHEN cost accountant membuat allocation rule THEN THE Cost_Center_System SHALL menyimpan source cost center, target cost center(s), allocation base, dan persentase/formula
2. THE Cost_Center_System SHALL mendukung allocation base: Direct (langsung), Square Footage (luas ruangan), Headcount (jumlah pegawai), Patient Days (hari rawat), Service Volume (volume layanan), Revenue (pendapatan)
3. WHEN allocation base adalah persentase THEN THE Cost_Center_System SHALL memvalidasi total persentase ke semua target = 100%
4. WHEN allocation base adalah formula THEN THE Cost_Center_System SHALL memvalidasi bahwa formula dapat dievaluasi dengan data yang tersedia
5. THE Cost_Center_System SHALL mendukung multi-step allocation (alokasi bertingkat)
6. WHEN allocation rule aktif THEN THE Cost_Center_System SHALL menerapkan rule secara otomatis pada periode penutupan
7. THE Cost_Center_System SHALL mencatat hasil alokasi dalam allocation journal untuk audit trail

### Requirement 5: Direct Cost Assignment

**User Story:** Sebagai accounting staff, saya ingin mengassign biaya langsung ke cost center yang tepat, sehingga biaya dapat ditelusuri ke sumbernya.

#### Acceptance Criteria

1. WHEN transaksi biaya dicatat THEN THE Cost_Center_System SHALL memvalidasi bahwa cost center yang dipilih aktif dan valid
2. THE Cost_Center_System SHALL mendukung assignment biaya ke cost center berdasarkan: Gaji pegawai (dari HR assignment), Pembelian material (dari purchase order), Pemakaian utilitas (dari meter reading), Depresiasi aset (dari asset location)
3. WHEN biaya gaji diposting THEN THE Cost_Center_System SHALL mengalokasikan ke cost center berdasarkan HR assignment percentage
4. WHEN biaya aset diposting THEN THE Cost_Center_System SHALL mengalokasikan ke cost center berdasarkan lokasi aset
5. THE Cost_Center_System SHALL memvalidasi bahwa setiap biaya memiliki cost center assignment sebelum posting
6. WHEN cost center assignment salah THEN THE Cost_Center_System SHALL menyediakan fungsi reclassification dengan approval
7. THE Cost_Center_System SHALL menyediakan laporan biaya langsung per cost center per periode

### Requirement 6: Indirect Cost Allocation

**User Story:** Sebagai cost accountant, saya ingin mengalokasikan biaya tidak langsung dari administrative cost center ke operational cost center, sehingga total biaya operasional mencerminkan true cost.

#### Acceptance Criteria

1. WHEN periode akuntansi ditutup THEN THE Cost_Center_System SHALL menjalankan proses alokasi biaya tidak langsung secara otomatis
2. THE Cost_Center_System SHALL mengalokasikan biaya dari Administrative Cost Center ke Medical dan Non-Medical Cost Center berdasarkan allocation rules
3. THE Cost_Center_System SHALL mendukung step-down allocation method (alokasi bertahap dari support ke operational)
4. WHEN alokasi dilakukan THEN THE Cost_Center_System SHALL mencatat allocation journal dengan detail: source, target, amount, allocation base, calculation
5. THE Cost_Center_System SHALL memvalidasi bahwa total biaya setelah alokasi sama dengan total biaya sebelum alokasi (zero-sum)
6. WHEN alokasi gagal validasi THEN THE Cost_Center_System SHALL rollback dan menampilkan error detail
7. THE Cost_Center_System SHALL menyediakan laporan allocation summary dan detail per periode

### Requirement 7: Cost Pool Management

**User Story:** Sebagai cost accountant, saya ingin mengelola cost pool untuk mengagregasi biaya sejenis sebelum alokasi, sehingga proses alokasi lebih efisien dan akurat.

#### Acceptance Criteria

1. WHEN cost accountant membuat cost pool THEN THE Cost_Center_System SHALL menyimpan nama pool, deskripsi, cost centers yang berkontribusi, dan allocation base
2. THE Cost_Center_System SHALL mendukung cost pool untuk: Utilities (listrik, air, gas), Facility (gedung, maintenance), IT Services, HR Services, Finance Services
3. WHEN biaya diposting ke cost center dalam pool THEN THE Cost_Center_System SHALL mengakumulasi biaya ke cost pool
4. WHEN cost pool dialokasikan THEN THE Cost_Center_System SHALL mendistribusikan total pool ke target cost centers berdasarkan allocation base
5. THE Cost_Center_System SHALL memvalidasi bahwa setiap cost pool memiliki allocation rule yang valid
6. WHEN cost pool di-nonaktifkan THEN THE Cost_Center_System SHALL mencegah akumulasi biaya baru
7. THE Cost_Center_System SHALL menyediakan laporan cost pool balance dan allocation history

### Requirement 8: Responsibility Center Performance

**User Story:** Sebagai direktur, saya ingin memonitor kinerja responsibility center berdasarkan budget vs actual, sehingga dapat mengevaluasi efisiensi operasional.

#### Acceptance Criteria

1. WHEN responsibility center adalah Profit Center THEN THE Cost_Center_System SHALL melacak pendapatan, biaya, dan profit margin
2. THE Cost_Center_System SHALL menyediakan dashboard kinerja per responsibility center dengan metrik: Total Cost, Cost per Unit, Budget Variance, Efficiency Ratio
3. WHEN periode akuntansi selesai THEN THE Cost_Center_System SHALL menghitung variance analysis (budget vs actual)
4. THE Cost_Center_System SHALL mengklasifikasikan variance sebagai: Favorable (menguntungkan) atau Unfavorable (tidak menguntungkan)
5. WHEN variance melebihi threshold THEN THE Cost_Center_System SHALL mengirim notifikasi ke cost center manager
6. THE Cost_Center_System SHALL menyediakan trend analysis untuk biaya per cost center (month-over-month, year-over-year)
7. THE Cost_Center_System SHALL mendukung drill-down dari summary ke detail transaksi biaya

### Requirement 9: Service Line Costing

**User Story:** Sebagai CFO, saya ingin menghitung biaya per service line, sehingga dapat menganalisis profitabilitas per lini layanan dan membuat keputusan strategis.

#### Acceptance Criteria

1. WHEN service line didefinisikan THEN THE Cost_Center_System SHALL mengaitkan multiple cost centers ke satu service line
2. THE Cost_Center_System SHALL mengagregasi biaya dari semua cost centers dalam service line
3. WHEN service line memiliki shared cost centers THEN THE Cost_Center_System SHALL mengalokasikan biaya berdasarkan service volume atau revenue contribution
4. THE Cost_Center_System SHALL menghitung total cost per service line per periode
5. WHEN pendapatan tersedia THEN THE Cost_Center_System SHALL menghitung profit margin per service line
6. THE Cost_Center_System SHALL menyediakan comparative analysis antar service lines
7. THE Cost_Center_System SHALL mendukung what-if analysis untuk perubahan allocation rules atau volume

### Requirement 10: Integration dengan Master Data Management

**User Story:** Sebagai system integrator, saya ingin memastikan cost center terintegrasi dengan organization unit dari MDM, sehingga perubahan struktur organisasi tercermin dalam cost center.

#### Acceptance Criteria

1. WHEN organization unit dari MDM diubah THEN THE Cost_Center_System SHALL menerima event notification dan memperbarui cost center terkait
2. THE Cost_Center_System SHALL memvalidasi bahwa organization unit yang dipilih untuk cost center ada dan aktif di MDM
3. WHEN organization unit di-nonaktifkan di MDM THEN THE Cost_Center_System SHALL menonaktifkan cost center terkait
4. THE Cost_Center_System SHALL menggunakan hierarchy path dari MDM untuk membangun cost center hierarchy
5. WHEN HR assignment berubah di MDM THEN THE Cost_Center_System SHALL memperbarui direct cost allocation untuk gaji
6. WHEN asset location berubah di MDM THEN THE Cost_Center_System SHALL memperbarui direct cost allocation untuk depresiasi
7. THE Cost_Center_System SHALL menyediakan reconciliation report antara organization units dan cost centers

### Requirement 11: Cost Center Budgeting

**User Story:** Sebagai budget manager, saya ingin menetapkan budget per cost center, sehingga dapat mengontrol pengeluaran dan melakukan variance analysis.

#### Acceptance Criteria

1. WHEN budget ditetapkan THEN THE Cost_Center_System SHALL menyimpan budget amount per cost center per periode (bulanan/tahunan)
2. THE Cost_Center_System SHALL mendukung budget breakdown per kategori biaya: Personnel, Supplies, Services, Depreciation, Overhead
3. WHEN biaya diposting THEN THE Cost_Center_System SHALL mengurangi available budget dan menghitung budget utilization percentage
4. WHEN budget utilization melebihi threshold (misal 80%) THEN THE Cost_Center_System SHALL mengirim warning ke cost center manager
5. THE Cost_Center_System SHALL mendukung budget revision dengan approval workflow
6. WHEN periode berakhir THEN THE Cost_Center_System SHALL menghitung budget variance (actual - budget) per cost center
7. THE Cost_Center_System SHALL menyediakan budget performance report dengan variance analysis

### Requirement 12: Audit Trail dan Compliance

**User Story:** Sebagai internal auditor, saya ingin melacak semua perubahan pada cost center dan allocation rules, sehingga dapat memastikan compliance dan akuntabilitas.

#### Acceptance Criteria

1. WHEN cost center dibuat, diubah, atau dihapus THEN THE Cost_Center_System SHALL mencatat audit log dengan user, timestamp, dan perubahan data
2. WHEN allocation rule dibuat atau diubah THEN THE Cost_Center_System SHALL mencatat audit log dengan detail rule dan justifikasi
3. WHEN alokasi biaya dijalankan THEN THE Cost_Center_System SHALL mencatat allocation journal dengan detail perhitungan
4. THE Cost_Center_System SHALL menyediakan audit trail report untuk periode tertentu
5. WHEN cost center reclassification dilakukan THEN THE Cost_Center_System SHALL memerlukan approval dan mencatat justifikasi
6. THE Cost_Center_System SHALL mendukung export audit log dalam format Excel dan PDF
7. THE Cost_Center_System SHALL menyimpan audit log minimal 5 tahun sesuai regulasi BLUD

### Requirement 13: Reporting dan Analytics

**User Story:** Sebagai management, saya ingin mendapatkan laporan dan analisis cost center yang komprehensif, sehingga dapat membuat keputusan berbasis data.

#### Acceptance Criteria

1. THE Cost_Center_System SHALL menyediakan laporan: Cost Center Summary, Cost Allocation Detail, Budget vs Actual, Variance Analysis, Trend Analysis
2. WHEN laporan diminta THEN THE Cost_Center_System SHALL mendukung filter: periode, cost center type, service line, organization unit
3. THE Cost_Center_System SHALL menyediakan visualisasi: Cost distribution pie chart, Trend line chart, Budget variance bar chart
4. WHEN laporan digenerate THEN THE Cost_Center_System SHALL mendukung export dalam format: Excel, PDF, CSV
5. THE Cost_Center_System SHALL menyediakan dashboard real-time untuk monitoring biaya per cost center
6. WHEN user memiliki permission THEN THE Cost_Center_System SHALL menampilkan laporan sesuai level akses (cost center manager hanya lihat cost center-nya)
7. THE Cost_Center_System SHALL mendukung scheduled report generation dan email distribution

### Requirement 14: Security dan Access Control

**User Story:** Sebagai security administrator, saya ingin mengontrol akses ke cost center data berdasarkan role, sehingga data sensitif hanya dapat diakses oleh user yang berwenang.

#### Acceptance Criteria

1. THE Cost_Center_System SHALL menerapkan permission: access cost-center-management, cost-center-management.view, cost-center-management.create, cost-center-management.edit, cost-center-management.delete
2. WHEN user mengakses cost center data THEN THE Cost_Center_System SHALL memvalidasi permission sesuai operasi
3. THE Cost_Center_System SHALL mendukung row-level security: cost center manager hanya dapat view/edit cost center yang dikelolanya
4. WHEN user tidak memiliki permission THEN THE Cost_Center_System SHALL menampilkan error 403 Forbidden
5. THE Cost_Center_System SHALL mencatat semua akses ke cost center data dalam audit log
6. WHEN allocation rule diubah THEN THE Cost_Center_System SHALL memerlukan permission khusus (cost-center-management.allocate)
7. THE Cost_Center_System SHALL mendukung approval workflow untuk operasi kritis (reclassification, allocation rule changes)

### Requirement 15: Data Validation dan Consistency

**User Story:** Sebagai data administrator, saya ingin sistem memvalidasi konsistensi data cost center, sehingga dapat mencegah error dalam perhitungan biaya.

#### Acceptance Criteria

1. WHEN cost center dibuat atau diubah THEN THE Cost_Center_System SHALL memvalidasi mandatory fields: code, name, type, organization_unit_id
2. THE Cost_Center_System SHALL memvalidasi bahwa kode cost center unik dalam sistem
3. WHEN allocation rule dibuat THEN THE Cost_Center_System SHALL memvalidasi bahwa source dan target cost centers berbeda
4. THE Cost_Center_System SHALL memvalidasi bahwa total allocation percentage = 100% untuk percentage-based allocation
5. WHEN alokasi dijalankan THEN THE Cost_Center_System SHALL memvalidasi zero-sum (total biaya sebelum = total biaya sesudah)
6. THE Cost_Center_System SHALL menyediakan data consistency check report
7. WHEN validasi gagal THEN THE Cost_Center_System SHALL menampilkan pesan error yang jelas dan actionable
