<?php

namespace App\Exports;

use App\Models\BankPertanyaan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BankPertanyaanExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Hanya ambil data yang diperlukan (jangan export ID/UUID)
        return BankPertanyaan::select('pertanyaan', 'butir_pertanyaan', 'dokumen_cek')->get();
    }

    public function headings(): array
    {
        // Judul baris pertama di Excel (Wajib sama dengan saat proses Import nanti)
        return [
            'pertanyaan',
            'butir_pertanyaan',
            'dokumen_cek'
        ];
    }

    public function map($row): array
    {
        return [
            $row->pertanyaan,
            $row->butir_pertanyaan,
            $row->dokumen_cek
        ];
    }
}