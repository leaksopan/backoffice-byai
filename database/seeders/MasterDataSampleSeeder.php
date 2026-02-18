<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\Models\MdmFundingSource;

class MasterDataSampleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOrganizationUnits();
        $this->seedChartOfAccounts();
        $this->seedFundingSources();
    }

    private function seedOrganizationUnits(): void
    {
        // Root level - Instalasi
        $rawatJalan = MdmOrganizationUnit::create([
            'code' => 'INS-RJ',
            'name' => 'Instalasi Rawat Jalan',
            'type' => 'installation',
            'parent_id' => null,
            'hierarchy_path' => 'INS-RJ',
            'level' => 0,
            'is_active' => true,
            'description' => 'Instalasi pelayanan rawat jalan',
        ]);

        $rawatInap = MdmOrganizationUnit::create([
            'code' => 'INS-RI',
            'name' => 'Instalasi Rawat Inap',
            'type' => 'installation',
            'parent_id' => null,
            'hierarchy_path' => 'INS-RI',
            'level' => 0,
            'is_active' => true,
            'description' => 'Instalasi pelayanan rawat inap',
        ]);

        $igd = MdmOrganizationUnit::create([
            'code' => 'INS-IGD',
            'name' => 'Instalasi Gawat Darurat',
            'type' => 'installation',
            'parent_id' => null,
            'hierarchy_path' => 'INS-IGD',
            'level' => 0,
            'is_active' => true,
            'description' => 'Instalasi gawat darurat',
        ]);

        // Child units - Rawat Jalan
        MdmOrganizationUnit::create([
            'code' => 'RJ-POLI-UMUM',
            'name' => 'Poli Umum',
            'type' => 'unit',
            'parent_id' => $rawatJalan->id,
            'hierarchy_path' => 'INS-RJ/RJ-POLI-UMUM',
            'level' => 1,
            'is_active' => true,
            'description' => 'Poliklinik umum',
        ]);

        MdmOrganizationUnit::create([
            'code' => 'RJ-POLI-GIGI',
            'name' => 'Poli Gigi',
            'type' => 'unit',
            'parent_id' => $rawatJalan->id,
            'hierarchy_path' => 'INS-RJ/RJ-POLI-GIGI',
            'level' => 1,
            'is_active' => true,
            'description' => 'Poliklinik gigi',
        ]);

        // Child units - Rawat Inap
        MdmOrganizationUnit::create([
            'code' => 'RI-KELAS-1',
            'name' => 'Ruang Rawat Inap Kelas 1',
            'type' => 'unit',
            'parent_id' => $rawatInap->id,
            'hierarchy_path' => 'INS-RI/RI-KELAS-1',
            'level' => 1,
            'is_active' => true,
            'description' => 'Ruang rawat inap kelas 1',
        ]);

        MdmOrganizationUnit::create([
            'code' => 'RI-KELAS-2',
            'name' => 'Ruang Rawat Inap Kelas 2',
            'type' => 'unit',
            'parent_id' => $rawatInap->id,
            'hierarchy_path' => 'INS-RI/RI-KELAS-2',
            'level' => 1,
            'is_active' => true,
            'description' => 'Ruang rawat inap kelas 2',
        ]);
    }

    private function seedChartOfAccounts(): void
    {
        // Aset (1-XX-XX-XX-XXX)
        $aset = MdmChartOfAccount::create([
            'code' => '1-00-00-00-000',
            'name' => 'ASET',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => null,
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $asetLancar = MdmChartOfAccount::create([
            'code' => '1-01-00-00-000',
            'name' => 'Aset Lancar',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $aset->id,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '1-01-01-00-000',
            'name' => 'Kas',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $asetLancar->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '1-01-02-00-000',
            'name' => 'Bank',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $asetLancar->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '1-01-03-00-000',
            'name' => 'Piutang Usaha',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $asetLancar->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        // Kewajiban (2-XX-XX-XX-XXX)
        $kewajiban = MdmChartOfAccount::create([
            'code' => '2-00-00-00-000',
            'name' => 'KEWAJIBAN',
            'category' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => null,
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $kewajibanJangkaPendek = MdmChartOfAccount::create([
            'code' => '2-01-00-00-000',
            'name' => 'Kewajiban Jangka Pendek',
            'category' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $kewajiban->id,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '2-01-01-00-000',
            'name' => 'Utang Usaha',
            'category' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $kewajibanJangkaPendek->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        // Ekuitas (3-XX-XX-XX-XXX)
        $ekuitas = MdmChartOfAccount::create([
            'code' => '3-00-00-00-000',
            'name' => 'EKUITAS',
            'category' => 'equity',
            'normal_balance' => 'credit',
            'parent_id' => null,
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '3-01-00-00-000',
            'name' => 'Modal',
            'category' => 'equity',
            'normal_balance' => 'credit',
            'parent_id' => $ekuitas->id,
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
        ]);

        // Pendapatan (4-XX-XX-XX-XXX)
        $pendapatan = MdmChartOfAccount::create([
            'code' => '4-00-00-00-000',
            'name' => 'PENDAPATAN',
            'category' => 'revenue',
            'normal_balance' => 'credit',
            'parent_id' => null,
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $pendapatanUsaha = MdmChartOfAccount::create([
            'code' => '4-01-00-00-000',
            'name' => 'Pendapatan Usaha',
            'category' => 'revenue',
            'normal_balance' => 'credit',
            'parent_id' => $pendapatan->id,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '4-01-01-00-000',
            'name' => 'Pendapatan Rawat Jalan',
            'category' => 'revenue',
            'normal_balance' => 'credit',
            'parent_id' => $pendapatanUsaha->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '4-01-02-00-000',
            'name' => 'Pendapatan Rawat Inap',
            'category' => 'revenue',
            'normal_balance' => 'credit',
            'parent_id' => $pendapatanUsaha->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        // Beban (5-XX-XX-XX-XXX)
        $beban = MdmChartOfAccount::create([
            'code' => '5-00-00-00-000',
            'name' => 'BEBAN',
            'category' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => null,
            'level' => 0,
            'is_header' => true,
            'is_active' => true,
        ]);

        $bebanOperasional = MdmChartOfAccount::create([
            'code' => '5-01-00-00-000',
            'name' => 'Beban Operasional',
            'category' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $beban->id,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '5-01-01-00-000',
            'name' => 'Beban Gaji',
            'category' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $bebanOperasional->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '5-01-02-00-000',
            'name' => 'Beban Obat dan BMHP',
            'category' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $bebanOperasional->id,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);
    }

    private function seedFundingSources(): void
    {
        MdmFundingSource::create([
            'code' => 'APBN-2026',
            'name' => 'APBN Tahun 2026',
            'type' => 'apbn',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'description' => 'Anggaran Pendapatan dan Belanja Negara tahun 2026',
        ]);

        MdmFundingSource::create([
            'code' => 'APBD-PROV-2026',
            'name' => 'APBD Provinsi Tahun 2026',
            'type' => 'apbd_provinsi',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'description' => 'Anggaran Pendapatan dan Belanja Daerah Provinsi tahun 2026',
        ]);

        MdmFundingSource::create([
            'code' => 'PNBP-2026',
            'name' => 'PNBP Tahun 2026',
            'type' => 'pnbp',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'description' => 'Penerimaan Negara Bukan Pajak tahun 2026',
        ]);

        MdmFundingSource::create([
            'code' => 'HIBAH-2026',
            'name' => 'Hibah Tahun 2026',
            'type' => 'hibah',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'description' => 'Dana hibah dari berbagai sumber tahun 2026',
        ]);
    }
}
