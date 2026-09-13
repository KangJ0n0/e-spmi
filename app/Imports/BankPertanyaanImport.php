<?php

namespace App\Imports;

use App\Models\BankPertanyaan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankPertanyaanImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0; // Menyimpan jumlah baris yang berhasil diimport

    // QOL fix (12 Sep 2026): dulu tidak ada jejak sama sekali soal baris mana yang dilewati atau
    // kenapa - Admin cuma tahu "berhasil import N butir" tanpa tahu kalau ternyata ada beberapa
    // baris yang diam-diam gagal/dilewati. Tiap entri: ['baris' => nomor baris asli di file Excel,
    // 'tipe' => 'info' (dilewati sengaja, bukan masalah) atau 'peringatan' (kemungkinan data
    // hilang, perlu dicek manual), 'alasan' => teks penjelasan]. Dibaca BankPertanyaanController::
    // importExcel() buat dikirim balik ke frontend.
    public array $skipped = [];

    public int $totalBaris = 0; // total baris data yang diproses (di luar baris judul), buat konteks di frontend

    // Kategori instrumen (mis. LAMEMBA) yang dipilih Admin SEBELUM upload (9 Sep 2026) -
    // NULLABLE, kalau nggak dipilih semua soal hasil import ini "Tanpa Kategori" seperti
    // sebelum fitur ini ada. Lihat BankPertanyaanController::importExcel().
    protected ?string $kategoriInstrumenId;

    public function __construct(?string $kategoriInstrumenId = null)
    {
        $this->kategoriInstrumenId = $kategoriInstrumenId;
    }

    // WAJIB: Kasih tahu Laravel Excel kalau judul kolom ada di Baris ke-2 (Row 2)
    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        $bankData = [];
        $currentIndex = -1;
        $this->totalBaris = $rows->count();

        foreach ($rows as $rowIndex => $row) {
            // headingRow() = 2 (judul kolom di baris 2), jadi $rows di sini sudah TIDAK termasuk
            // baris judul dan 0-indexed mulai dari baris data pertama -> baris Excel asli = index + 3.
            $baris = $rowIndex + 3;

            // Ambil data dengan penanganan penulisan key (antisipasi spasi berlebih dari Excel)
            $pertanyaan = trim($row['pernyataan_isi_standar'] ?? '');
            $butir      = trim($row['butir_pertanyaan'] ?? '');
            // Antisipasi tulisan 'dicheck' atau 'dicek'
            $dokumen    = trim($row['dokumen_akan_dicheck'] ?? $row['dokumen_akan_dicek'] ?? '');

            // Baris benar-benar kosong semua kolom (spacer/jarak di Excel) - dilewati diam-diam,
            // TIDAK dilaporkan (bukan anomali, memang tidak ada apa-apa di baris ini).
            if ($pertanyaan === '' && $butir === '' && $dokumen === '') {
                continue;
            }

            // 1. Lewati baris indeks numerik dosen (baris di bawah judul yang isinya cuma angka 1, 2, 3, 4)
            if ($pertanyaan == '2' && $butir == '3') {
                $this->skipped[] = [
                    'baris'  => $baris,
                    'tipe'   => 'info',
                    'alasan' => 'Baris indeks nomor dosen (bagian dari format template Excel), otomatis dilewati - bukan error.',
                ];
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
            elseif (!empty($dokumen)) {
                if ($currentIndex >= 0) {
                    $bankData[$currentIndex]['dokumen_cek'] .= "\n" . $dokumen;
                } else {
                    // QOL fix (12 Sep 2026): dulu diam-diam tidak melakukan apa-apa (data hilang
                    // tanpa jejak). Kasus ini berarti ada isi "Dokumen yang Akan Dicek" tapi belum
                    // ada soal sebelumnya buat digabung - kemungkinan baris pertama sheet salah
                    // format atau merge cell-nya tidak standar.
                    $this->skipped[] = [
                        'baris'  => $baris,
                        'tipe'   => 'peringatan',
                        'alasan' => "Kolom 'Dokumen yang Akan Dicek' terisi (\"" . Str::limit($dokumen, 60) . "\") tapi tidak ada 'Pernyataan Isi Standar' sebelumnya untuk digabungkan - baris ini dilewati.",
                    ];
                }
            }
            // 4. Sisa kasus: pertanyaan & dokumen kosong tapi butir_pertanyaan ada isinya - tidak
            // match pola manapun (bukan soal baru, tidak bisa digabung) - QOL fix (12 Sep 2026):
            // dulu datanya hilang diam-diam di sini juga.
            elseif (!empty($butir)) {
                $this->skipped[] = [
                    'baris'  => $baris,
                    'tipe'   => 'peringatan',
                    'alasan' => "Kolom 'Butir Pertanyaan' terisi (\"" . Str::limit($butir, 60) . "\") tapi 'Pernyataan Isi Standar' dan 'Dokumen yang Akan Dicek' kosong - baris ini dilewati, cek manual apakah ada kesalahan pengisian Excel.",
                ];
            }
        }

        // 5. Simpan ke Database
        foreach ($bankData as $data) {
            $data['kategori_instrumen_id'] = $this->kategoriInstrumenId;
            BankPertanyaan::create($data);
            $this->importedCount++;
        }
    }
}