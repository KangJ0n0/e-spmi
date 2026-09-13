<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankPertanyaan;
use App\Imports\BankPertanyaanImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        $query = BankPertanyaan::with('kategoriInstrumen')->latest();

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
            $import = new BankPertanyaanImport($request->input('kategori_instrumen_id'));
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
}