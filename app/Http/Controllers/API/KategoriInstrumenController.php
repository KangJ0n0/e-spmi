<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KategoriInstrumen;
use Illuminate\Http\Request;

/**
 * Kelola "ruang" kategori instrumen (contoh: LAMEMBA) - fitur baru 9 Sep 2026 diminta client
 * biar Admin bisa bikin kategori soal sendiri di luar Instrumen bawaan, lalu upload/tambah
 * soal khusus di situ. Dipakai dari KategoriInstrumenModal.vue (halaman Instrumen, Admin) buat
 * CRUD kategorinya, dan dropdown filter kategori di BankPertanyaan.vue &
 * PilihPertanyaanAuditor.vue.
 */
class KategoriInstrumenController extends Controller
{
    // READ: daftar semua kategori, diurutkan nama biar dropdown-nya rapi/konsisten.
    public function index()
    {
        return response()->json(KategoriInstrumen::orderBy('nama')->get());
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_instrumen,nama',
        ], [
            'nama.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $kategori = KategoriInstrumen::create($validated);
        return response()->json(['message' => 'Kategori instrumen berhasil ditambahkan', 'data' => $kategori], 201);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $kategori = KategoriInstrumen::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_instrumen,nama,' . $id,
        ], [
            'nama.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $kategori->update($validated);
        return response()->json(['message' => 'Kategori instrumen berhasil diupdate', 'data' => $kategori]);
    }

    // DELETE - soal yang sudah masuk kategori ini TIDAK ikut terhapus, otomatis balik jadi
    // "Tanpa Kategori" lewat `nullOnDelete()` di migration kolom `kategori_instrumen_id`.
    public function destroy($id)
    {
        KategoriInstrumen::findOrFail($id)->delete();
        return response()->json(['message' => 'Kategori instrumen berhasil dihapus']);
    }
}
