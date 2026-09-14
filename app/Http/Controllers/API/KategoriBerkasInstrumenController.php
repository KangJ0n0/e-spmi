<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KategoriBerkasInstrumen;
use Illuminate\Http\Request;

/**
 * Kelola kategori "Berkas Instrumen" (fitur baru 13 Sep 2026) - TERPISAH dari
 * KategoriInstrumenController (kategori punya Bank Pertanyaan), dikonfirmasi user. Pola CRUD
 * sama persis dengan KategoriInstrumenController.
 */
class KategoriBerkasInstrumenController extends Controller
{
    public function index()
    {
        return response()->json(KategoriBerkasInstrumen::orderBy('nama')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_berkas_instrumen,nama',
        ], [
            'nama.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $kategori = KategoriBerkasInstrumen::create($validated);
        return response()->json(['message' => 'Kategori berkas instrumen berhasil ditambahkan', 'data' => $kategori], 201);
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriBerkasInstrumen::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_berkas_instrumen,nama,' . $id,
        ], [
            'nama.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $kategori->update($validated);
        return response()->json(['message' => 'Kategori berkas instrumen berhasil diupdate', 'data' => $kategori]);
    }

    // DELETE - berkas yang sudah masuk kategori ini TIDAK ikut terhapus, otomatis balik jadi
    // "Tanpa Kategori" lewat `nullOnDelete()` di migration kolom `kategori_berkas_instrumen_id`.
    public function destroy($id)
    {
        KategoriBerkasInstrumen::findOrFail($id)->delete();
        return response()->json(['message' => 'Kategori berkas instrumen berhasil dihapus']);
    }
}
