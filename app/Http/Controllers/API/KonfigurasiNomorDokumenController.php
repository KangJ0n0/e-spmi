<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KonfigurasiNomorDokumen;
use Illuminate\Http\Request;

/**
 * Kelola Nomor Dokumen Instrumen 1-6 PER PERIODE (semester) - fitur baru 13 Sep 2026, lihat
 * docblock lengkap di migration `create_konfigurasi_nomor_dokumen_table`. Dipakai dari halaman
 * Admin "Konfigurasi Dokumen" (KonfigurasiNomorDokumen.vue). Nomor yang tersimpan di sini
 * dikonsumsi otomatis oleh DokumenAuditController::generate() lewat resolveNomorDokumen() -
 * kalau Admin belum isi (atau belum bikin konfigurasi buat semester tertentu), Instrumen yang
 * bersangkutan tetap jalan pakai nomor default lama.
 */
class KonfigurasiNomorDokumenController extends Controller
{
    // READ: semua konfigurasi yang pernah dibuat, diurutkan semester terbaru duluan.
    public function index()
    {
        return response()->json(KonfigurasiNomorDokumen::orderByDesc('semester')->get());
    }

    // CREATE atau UPDATE sekaligus (upsert by semester) - Admin nggak perlu tau apakah semester
    // itu sudah pernah dikonfigurasi sebelumnya atau belum, tinggal isi form yang sama.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'semester'        => 'required|string|max:10',
            'nomor_dokumen_1' => 'nullable|string|max:255',
            'nomor_dokumen_2' => 'nullable|string|max:255',
            'nomor_dokumen_3' => 'nullable|string|max:255',
            'nomor_dokumen_4' => 'nullable|string|max:255',
            'nomor_dokumen_5' => 'nullable|string|max:255',
            'nomor_dokumen_6' => 'nullable|string|max:255',
        ]);

        $konfigurasi = KonfigurasiNomorDokumen::updateOrCreate(
            ['semester' => $validated['semester']],
            $validated,
        );

        return response()->json([
            'message' => 'Konfigurasi nomor dokumen berhasil disimpan',
            'data'    => $konfigurasi,
        ], 200);
    }

    public function destroy($id)
    {
        KonfigurasiNomorDokumen::findOrFail($id)->delete();
        return response()->json(['message' => 'Konfigurasi nomor dokumen berhasil dihapus']);
    }
}
