<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankPertanyaan;
use App\Imports\BankPertanyaanImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BankPertanyaanController extends Controller
{
    // READ: Ambil semua data
    public function index()
    {
        return response()->json(BankPertanyaan::latest()->get());
    }

    // CREATE: Tambah satu pertanyaan manual
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pertanyaan'       => 'required|string',
            'butir_pertanyaan' => 'required|string',
            'dokumen_cek'      => 'required|string',
        ]);

        $bank = BankPertanyaan::create($validated);
        return response()->json(['message' => 'Pertanyaan berhasil ditambahkan', 'data' => $bank], 201);
    }

    // UPDATE: Edit pertanyaan
    public function update(Request $request, $id)
    {
        $bank = BankPertanyaan::findOrFail($id);
        
        $validated = $request->validate([
            'pertanyaan'       => 'required|string',
            'butir_pertanyaan' => 'required|string',
            'dokumen_cek'      => 'required|string',
        ]);

        $bank->update($validated);
        return response()->json(['message' => 'Pertanyaan berhasil diupdate', 'data' => $bank]);
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
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            $import = new BankPertanyaanImport();
            Excel::import($import, $request->file('file'));

            // Jika masih 0, beri tahu admin agar cek format Excel
            if ($import->importedCount === 0) {
                return response()->json([
                    'message' => 'Import selesai, namun 0 data berhasil masuk. Pastikan judul kolom Excel ada di baris ke-2!'
                ], 400);
            }

            return response()->json([
                'message' => 'Berhasil import ' . $import->importedCount . ' butir pertanyaan ke database!'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal import data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}