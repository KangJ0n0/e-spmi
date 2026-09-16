<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankPertanyaan;
use App\Imports\BankPertanyaanImport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BankPertanyaanController extends Controller
{
    // READ: Ambil semua data (atau difilter per kategori instrumen)
    public function index(Request $request)
    {
        // Filter kategori (fitur baru 9 Sep 2026) - dipakai halaman Instrumen buat lihat soal
        // per kategori (mis. LAMEMBA), dan Pilih Pertanyaan Auditor buat milih per kategori.
        // ?kategori_instrumen_id=<uuid> -> soal di kategori itu saja.
        // ?kategori_instrumen_id=none   -> soal yang belum dikategorikan ("Tanpa Kategori").
        // Tanpa param sama sekali -> SEMUA soal (perilaku lama, TIDAK berubah).
        // Urutan (15 Sep 2026) - dulu ->latest() (soal terbaru duluan), jadi hasil import (banyak
        // baris masuk dalam 1 request yang sama) malah tampil TERBALIK dari urutan aslinya di
        // Excel (baris terakhir kebaca duluan) - dilaporkan user "aneh dilihat". Diganti ->oldest()
        // (dibuat paling awal duluan) - soal hasil import tampil URUT SAMA seperti baris di Excel
        // sumbernya, soal baru (manual/import berikutnya) nambah di paling BAWAH, bukan di atas.
        $query = BankPertanyaan::with('kategoriInstrumen')->oldest();

        if ($request->filled('kategori_instrumen_id')) {
            if ($request->input('kategori_instrumen_id') === 'none') {
                $query->whereNull('kategori_instrumen_id');
            } else {
                $query->where('kategori_instrumen_id', $request->input('kategori_instrumen_id'));
            }
        }

        // Pencarian teks (QOL fix 12 Sep 2026) - dicari di 3 kolom utama sekaligus, dipakai
        // bareng pagination di bawah supaya halaman Instrumen (Admin) bisa nyaring dari ribuan
        // butir tanpa perlu narik semua data dulu.
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('pertanyaan', 'like', '%' . $filter . '%')
                    ->orWhere('butir_pertanyaan', 'like', '%' . $filter . '%')
                    ->orWhere('dokumen_cek', 'like', '%' . $filter . '%');
            });
        }

        // QOL fix (12 Sep 2026) - endpoint ini dulu SELALU `->get()` semua baris tanpa batas,
        // padahal ini halaman ETL yang menurut deskripsi proyek bisa berisi ribuan butir soal
        // (lihat catatan security/QOL review). Pagination CUMA aktif kalau parameter `paginate`
        // eksplisit dikirim - endpoint ini JUGA dipanggil PilihPertanyaanAuditor.vue TANPA
        // paginate sama sekali (butuh SEMUA soal hasil filter buat fitur "Kirim Semua per
        // Kategori" yang beroperasi di seluruh daftar, bukan 1 halaman) - perilaku itu SENGAJA
        // TIDAK diubah. Pola ini persis sama dengan yang sudah dipakai di
        // JadwalAuditController::index()/StrukturAnggotaController::index().
        $inputpaginate = $request->input('paginate');
        $inputlimit = $request->input('limit');

        $results = $inputpaginate === null
            ? ($inputlimit !== null ? $query->take($inputlimit)->get() : $query->get())
            : $query->paginate($inputpaginate);

        return response()->json($results);
    }

    // CREATE: Tambah satu pertanyaan manual
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pertanyaan'             => 'required|string',
            'butir_pertanyaan'       => 'required|string',
            'dokumen_cek'            => 'required|string',
            // Opsional (9 Sep 2026) - kalau nggak diisi, soal jadi "Tanpa Kategori" seperti sedia kala.
            'kategori_instrumen_id'  => 'nullable|exists:kategori_instrumen,id',
        ]);

        $bank = BankPertanyaan::create($validated);
        return response()->json(['message' => 'Pertanyaan berhasil ditambahkan', 'data' => $bank->load('kategoriInstrumen')], 201);
    }

    // UPDATE: Edit pertanyaan
    public function update(Request $request, $id)
    {
        $bank = BankPertanyaan::findOrFail($id);

        $validated = $request->validate([
            'pertanyaan'             => 'required|string',
            'butir_pertanyaan'       => 'required|string',
            'dokumen_cek'            => 'required|string',
            'kategori_instrumen_id'  => 'nullable|exists:kategori_instrumen,id',
        ]);

        $bank->update($validated);
        return response()->json(['message' => 'Pertanyaan berhasil diupdate', 'data' => $bank->load('kategoriInstrumen')]);
    }

    // DELETE: Hapus pertanyaan
    public function destroy($id)
    {
        BankPertanyaan::findOrFail($id)->delete();
        return response()->json(['message' => 'Pertanyaan berhasil dihapus']);
    }

    // DELETE MASSAL PER KATEGORI (fitur baru 15 Sep 2026) - user minta cara cepat kosongin 1
    // kategori (mis. abis import salah/mau ulang) tanpa hapus manual satu-satu.
    // ?kategori_instrumen_id=<uuid> -> hapus semua soal di kategori itu.
    // ?kategori_instrumen_id=none   -> hapus semua soal yang "Tanpa Kategori".
    // WAJIB diisi (bukan 'all'/kosong) - SENGAJA TIDAK ada mode "hapus semua tanpa pandang
    // kategori" di endpoint ini, biar nggak ada jalan (sengaja/nggak sengaja) buat nge-wipe
    // SELURUH bank soal cuma dari 1 tombol. Frontend (BankPertanyaan.vue) juga cuma nampilin
    // tombolnya kalau kategoriAktif spesifik - proteksi dobel, backend & frontend sama-sama jaga.
    public function hapusMassal(Request $request)
    {
        $request->validate([
            'kategori_instrumen_id' => 'required|string',
        ]);

        $kategoriId = $request->input('kategori_instrumen_id');

        $query = BankPertanyaan::query();
        if ($kategoriId === 'none') {
            $query->whereNull('kategori_instrumen_id');
        } else {
            // exists:kategori_instrumen,id di sini (bukan nullable) - beda dari validasi
            // store()/update() karena di sini WAJIB nunjuk 1 kategori yang beneran ada, nggak
            // boleh sembarang string.
            $request->validate(['kategori_instrumen_id' => 'exists:kategori_instrumen,id']);
            $query->where('kategori_instrumen_id', $kategoriId);
        }

        $jumlah = $query->count();
        $query->delete();

        return response()->json([
            'message' => "Berhasil menghapus {$jumlah} soal.",
            'jumlah_dihapus' => $jumlah,
        ]);
    }

    // IMPORT EXCEL
   public function importExcel(Request $request)
    {
        $request->validate([
            'file'                   => 'required|mimes:xlsx,xls,csv|max:5120',
            // Opsional (9 Sep 2026) - dipilih Admin di dropdown sebelum upload (lihat
            // BankPertanyaan.vue). Kalau kosong/"Semua Kategori" dipilih, semua soal hasil
            // import ini masuk "Tanpa Kategori", sama seperti sebelum fitur ini ada.
            'kategori_instrumen_id'  => 'nullable|exists:kategori_instrumen,id',
        ]);

        try {
            // Bugfix (15 Sep 2026) - lihat komentar lengkap di BankPertanyaanImport. Baris judul
            // kolom dideteksi otomatis per file (1 atau 2), bukan hardcode 2 lagi - jadi template
            // yang judul kolomnya langsung di baris 1 (tanpa baris judul dokumen di atasnya, mis.
            // "Lamspak-AP") sekarang juga kebaca, bukan cuma diam-diam 0 data masuk.
            $headingRow = $this->detectHeadingRow($request->file('file')->getRealPath());
            $import = new BankPertanyaanImport($request->input('kategori_instrumen_id'), $headingRow);
            Excel::import($import, $request->file('file'));

            // QOL fix (12 Sep 2026): dulu cuma balikin 1 dari 2 pesan generik (sukses dengan
            // angka total, atau gagal total) - Admin tidak pernah tahu baris mana yang di-skip
            // atau kenapa. Sekarang detail per baris (dari BankPertanyaanImport::$skipped) selalu
            // ikut dikirim balik, dipisah per 'tipe' biar frontend bisa bedain yang sekadar info
            // (baris indeks dosen - normal) dari yang peringatan (kemungkinan data hilang).
            $peringatan = array_values(array_filter($import->skipped, fn($s) => $s['tipe'] === 'peringatan'));
            $info = array_values(array_filter($import->skipped, fn($s) => $s['tipe'] === 'info'));

            // Jika masih 0, beri tahu admin agar cek format Excel
            if ($import->importedCount === 0) {
                return response()->json([
                    'message'          => 'Import selesai, namun 0 data berhasil masuk. Pastikan judul kolom Excel ada di baris ke-2!',
                    'total_baris'      => $import->totalBaris,
                    'berhasil'         => 0,
                    'peringatan'       => $peringatan,
                    'info'             => $info,
                ], 400);
            }

            return response()->json([
                'message'     => 'Berhasil import ' . $import->importedCount . ' butir pertanyaan ke database!',
                'total_baris' => $import->totalBaris,
                'berhasil'    => $import->importedCount,
                'peringatan'  => $peringatan,
                'info'        => $info,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal import data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Bugfix (15 Sep 2026) - deteksi otomatis baris judul kolom (1 atau 2). Cek baris 1: kalau
    // salah satu selnya, setelah di-slug, cocok dengan nama kolom yang dicari import (termasuk
    // varian "butir_pertanyaan_auditor" & "dokumen_akan_dicheck"/"dicek"), berarti judul kolom
    // memang di baris 1 (template tanpa baris judul dokumen, mis. "Lamspak-AP"). Kalau tidak ada
    // yang cocok, fallback ke baris 2 - perilaku LAMA tetap jalan persis sama seperti sebelumnya
    // buat semua template yang sudah biasa dipakai (baris 1 = judul dokumen, baris 2 = judul kolom).
    private function detectHeadingRow(string $path): int
    {
        $kolomDicari = [
            'pernyataan_isi_standar',
            'butir_pertanyaan',
            'butir_pertanyaan_auditor',
            'dokumen_akan_dicek',
            'dokumen_akan_dicheck',
        ];

        try {
            $sheet = IOFactory::load($path)->getActiveSheet();
            $baris1 = [];
            foreach ($sheet->getRowIterator(1, 1) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $nilai = trim((string) $cell->getValue());
                    if ($nilai !== '') {
                        $baris1[] = Str::slug($nilai, '_');
                    }
                }
            }
            if (count(array_intersect($baris1, $kolomDicari)) > 0) {
                return 1;
            }
        } catch (\Throwable $e) {
            // Gagal baca buat deteksi (mis. file corrupt) - biarkan fallback ke baris 2 di bawah,
            // Excel::import() di importExcel() yang bakal kasih tau kalau filenya beneran rusak.
        }

        return 2;
    }
}