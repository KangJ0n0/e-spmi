<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BerkasInstrumen;
use Illuminate\Http\Request;

/**
 * CRUD "Berkas Instrumen" (fitur baru 13 Sep 2026) - repositori link dokumen pendukung
 * instrumen (nama, keterangan, link - biasanya Google Drive). Halaman & endpoint ini Admin
 * only (dikonfirmasi user), beda dari Bank Pertanyaan yang baca-nya juga dipakai Auditor.
 */
class BerkasInstrumenController extends Controller
{
    public function index(Request $request)
    {
        // Filter kategori, pola sama persis dengan BankPertanyaanController::index():
        // ?kategori_berkas_instrumen_id=<uuid> -> berkas di kategori itu saja.
        // ?kategori_berkas_instrumen_id=none   -> berkas yang belum dikategorikan.
        // Tanpa param -> semua berkas.
        $query = BerkasInstrumen::with('kategoriBerkasInstrumen')->latest();

        if ($request->filled('kategori_berkas_instrumen_id')) {
            if ($request->input('kategori_berkas_instrumen_id') === 'none') {
                $query->whereNull('kategori_berkas_instrumen_id');
            } else {
                $query->where('kategori_berkas_instrumen_id', $request->input('kategori_berkas_instrumen_id'));
            }
        }

        $filter = $request->input('filter');
        if (!empty($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('nama', 'like', '%' . $filter . '%')
                    ->orWhere('keterangan', 'like', '%' . $filter . '%');
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'                          => 'required|string|max:255',
            'keterangan'                    => 'nullable|string',
            'link'                          => 'required|string|max:2048|url',
            'kategori_berkas_instrumen_id'  => 'nullable|exists:kategori_berkas_instrumen,id',
        ]);

        $berkas = BerkasInstrumen::create($validated);
        return response()->json(['message' => 'Berkas instrumen berhasil ditambahkan', 'data' => $berkas->load('kategoriBerkasInstrumen')], 201);
    }

    public function update(Request $request, $id)
    {
        $berkas = BerkasInstrumen::findOrFail($id);

        $validated = $request->validate([
            'nama'                          => 'required|string|max:255',
            'keterangan'                    => 'nullable|string',
            'link'                          => 'required|string|max:2048|url',
            'kategori_berkas_instrumen_id'  => 'nullable|exists:kategori_berkas_instrumen,id',
        ]);

        $berkas->update($validated);
        return response()->json(['message' => 'Berkas instrumen berhasil diupdate', 'data' => $berkas->load('kategoriBerkasInstrumen')]);
    }

    public function destroy($id)
    {
        BerkasInstrumen::findOrFail($id)->delete();
        return response()->json(['message' => 'Berkas instrumen berhasil dihapus']);
    }
}
