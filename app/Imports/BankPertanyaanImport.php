<?php

namespace App\Imports;

use App\Models\BankPertanyaan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankPertanyaanImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0; // Jumlah soal BARU yang ditambahkan
    public $timpaCount = 0; // Fix (17 Sep 2026) - jumlah soal LAMA yang ditimpa/diperbarui (lihat catatan di collection())

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

    // Bugfix (15 Sep 2026) - dulu di-hardcode 2 (asumsi selalu ada baris judul dokumen di baris 1,
    // baru judul kolom asli di baris 2). Ternyata ada template Instrumen (mis. "Lamspak-AP") yang
    // judul kolomnya LANGSUNG di baris 1, tanpa baris judul dokumen di atasnya. Kalau file kayak
    // gini diimport pakai asumsi lama, baris 2 (yang isinya SOAL PERTAMA, bukan judul kolom) malah
    // kepake jadi nama kolom -> semua nama kolom yang dicari (pernyataan_isi_standar, dst) nggak
    // pernah ketemu -> SEMUA baris soal dianggap kosong & di-skip diam-diam, importedCount = 0,
    // TANPA peringatan apapun ke Admin. Sekarang baris judul kolomnya dideteksi otomatis per file
    // (lihat BankPertanyaanController::detectHeadingRow()) dan dioper ke sini, bukan hardcode lagi.
    protected int $headingRowNum;

    public function __construct(?string $kategoriInstrumenId = null, int $headingRowNum = 2)
    {
        $this->kategoriInstrumenId = $kategoriInstrumenId;
        $this->headingRowNum = $headingRowNum;
    }

    public function headingRow(): int
    {
        return $this->headingRowNum;
    }

    public function collection(Collection $rows)
    {
        $bankData = [];
        $currentIndex = -1;
        $this->totalBaris = $rows->count();

        foreach ($rows as $rowIndex => $row) {
            // $rows di sini sudah TIDAK termasuk baris judul, 0-indexed mulai dari baris data
            // pertama -> baris Excel asli = index + headingRowNum + 1 (dulu di-hardcode +3, yang
            // cuma benar kalau headingRowNum selalu 2).
            $baris = $rowIndex + $this->headingRowNum + 1;

            // Ambil data dengan penanganan penulisan key (antisipasi spasi berlebih dari Excel)
            $pertanyaan = trim($row['pernyataan_isi_standar'] ?? '');
            // Bugfix (15 Sep 2026) - template "Lamspak-AP" nulis judul kolomnya "Butir Pertanyaan
            // (Auditor)", yang di-slug jadi 'butir_pertanyaan_auditor' (bukan 'butir_pertanyaan'
            // polos) - ditambah sebagai fallback, sama pola dengan antisipasi 'dicheck'/'dicek' di
            // kolom dokumen di bawah.
            $butir      = trim($row['butir_pertanyaan'] ?? $row['butir_pertanyaan_auditor'] ?? '');
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
        // Fix (17 Sep 2026, laporan Bu Cahyani/LPMU) - dulu tiap import SELALU bikin baris baru,
        // jadi kalau Admin upload ulang buat mengoreksi soal yang salah, soal lama & "revisi"-nya
        // numpuk jadi dobel. Sekarang tiap baris Excel dicocokkan ke soal yang SUDAH ADA di
        // kategori yang sama, berdasarkan teks 'Pernyataan Isi Standar' PERSIS SAMA: kalau ketemu
        // -> soal itu ditimpa (update butir_pertanyaan & dokumen_cek-nya, urutan lama tidak
        // berubah). Kalau tidak ketemu (soal baru) -> ditambahkan sebagai baris baru, dapat nomor
        // urutan lanjutan. $importedCount/$timpaCount dipakai BankPertanyaanController::
        // importExcel() buat kasih tahu Admin persis berapa yang ditambah vs ditimpa.
        //
        // Fix (17 Sep 2026) - urutan soal hasil import kadang beda dari urutan baris di Excel
        // aslinya (banyak baris masuk dalam waktu <1 detik, jadi `created_at`-nya sama semua,
        // MySQL nggak jamin urutan buat baris yang timestamp-nya identik). Sekarang tiap soal BARU
        // dikasih nomor `urutan` NAIK TERUS sesuai urutan diproses di sini (= urutan baris di
        // Excel), dipakai BankPertanyaanController::index() buat nentuin urutan tampil.
        $urutan = BankPertanyaan::max('urutan') ?? 0;
        foreach ($bankData as $data) {
            $query = BankPertanyaan::where('pertanyaan', $data['pertanyaan']);
            if ($this->kategoriInstrumenId) {
                $query->where('kategori_instrumen_id', $this->kategoriInstrumenId);
            } else {
                $query->whereNull('kategori_instrumen_id');
            }
            $existing = $query->first();

            if ($existing) {
                $existing->update([
                    'butir_pertanyaan' => $data['butir_pertanyaan'],
                    'dokumen_cek'      => $data['dokumen_cek'],
                ]);
                $this->timpaCount++;
            } else {
                $urutan++;
                $data['kategori_instrumen_id'] = $this->kategoriInstrumenId;
                $data['urutan'] = $urutan;
                BankPertanyaan::create($data);
                $this->importedCount++;
            }
        }
    }
}