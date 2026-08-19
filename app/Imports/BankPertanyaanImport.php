<?php

namespace App\Imports;

use App\Models\BankPertanyaan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankPertanyaanImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0; // Menyimpan jumlah baris yang berhasil diimport

    // WAJIB: Kasih tahu Laravel Excel kalau judul kolom ada di Baris ke-2 (Row 2)
    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        $bankData = [];
        $currentIndex = -1;

        foreach ($rows as $row) {
            // Ambil data dengan penanganan penulisan key (antisipasi spasi berlebih dari Excel)
            $pertanyaan = trim($row['pernyataan_isi_standar'] ?? '');
            $butir      = trim($row['butir_pertanyaan'] ?? '');
            // Antisipasi tulisan 'dicheck' atau 'dicek'
            $dokumen    = trim($row['dokumen_akan_dicheck'] ?? $row['dokumen_akan_dicek'] ?? '');

            // 1. Lewati baris indeks numerik dosen (baris di bawah judul yang isinya cuma angka 1, 2, 3, 4)
            if ($pertanyaan == '2' && $butir == '3') {
                continue;
            }

            // 2. Jika baris ini memiliki Pertanyaan utama
            if (!empty($pertanyaan)) {
                $bankData[] = [
                    'pertanyaan'       => $pertanyaan,
                    'butir_pertanyaan' => $butir,
                    'dokumen_cek'      => $dokumen,
                ];
                $currentIndex++;
            } 
            // 3. Jika pertanyaan kosong tapi dokumen ada -> gabungkan (efek Merge Cell Excel)
            else {
                if ($currentIndex >= 0 && !empty($dokumen)) {
                    $bankData[$currentIndex]['dokumen_cek'] .= "\n" . $dokumen;
                }
            }
        }

        // 4. Simpan ke Database
        foreach ($bankData as $data) {
            BankPertanyaan::create($data);
            $this->importedCount++;
        }
    }
}