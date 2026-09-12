<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\JadwalAudit; 

class JadwalAuditController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter');
        $inputpaginate = $request->input('paginate');
        $inputlimit = $request->input('limit');
        $inputsemester = $request->input('semester');
        // Dipakai buat deep-link "buka langsung ke jadwal ini" (mis. dari halaman
        // Auditor/Auditee) - kirim `id` biar balik cuma 1 baris, bukan ngefilter teks.
        $inputid = $request->input('id');

        // UBAH DI SINI: jadwal_spmi
        $query = DB::table('jadwal_spmi')
            ->select('id', 'tanggal_awal', 'tanggal_akhir', 'semester', 'nama_jadwal', 'area_audit')
            ->when($filter, function ($query) use ($filter) {
                $query->where('nama_jadwal', 'like', '%' . $filter . '%')
                    ->orWhere('area_audit', 'like', '%' . $filter . '%')
                    ->orWhere('semester', 'like', '%' . $filter . '%');
            });

        if (!empty($inputsemester)) {
            $query->where('semester', $inputsemester);
        }

        if (!empty($inputid)) {
            $query->where('id', $inputid);
        }

        $results = $inputpaginate === null
            ? ($inputlimit !== null ? $query->take($inputlimit)->get() : $query->get())
            : $query->paginate($inputpaginate);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_jadwal'  => 'required|max:255',
            'area_audit'   => 'required|max:255',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir'=> 'required|date|after_or_equal:tanggal_awal',
            'semester'     => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $nama_jadwal = $request->input('nama_jadwal');
        $area_audit  = $request->input('area_audit');
        $tanggal_awal= $request->input('tanggal_awal');
        $tanggal_akhir=$request->input('tanggal_akhir');
        $semester    = $request->input('semester');

        // Nama jadwal BOLEH sama (mis. "AMI" dipakai tiap semester) - yang nggak boleh sama
        // adalah kombinasi nama_jadwal + area_audit (itu baru dianggap jadwal duplikat).
        // Sebelumnya cek ini cuma lihat nama_jadwal sendirian, jadi nama yang sama sekalipun
        // area audit-nya beda (mis. "AMI" - Fakultas Ekonomi vs "AMI" - Fakultas Teknik) ikut
        // keblokir - padahal itu 2 jadwal yang sah beda.
        $checkData = DB::table('jadwal_spmi')
            ->where('nama_jadwal', $nama_jadwal)
            ->where('area_audit', $area_audit)
            ->first();
        if ($checkData) {
            return response()->json(['error' => 'Jadwal dengan nama & area audit yang sama sudah ada'], 400);
        }

        try {
            DB::beginTransaction();

            // Dibalikin objek jadwal yang baru dibuat (termasuk id-nya) - dipakai FE buat
            // langsung lompat ke mode Edit tanpa user harus balik ke daftar & klik Edit manual.
            $jadwal = JadwalAudit::create([
                'nama_jadwal'  => $nama_jadwal,
                'area_audit'   => $area_audit,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir'=> $tanggal_akhir,
                'semester'     => $semester,
            ]);

            DB::commit();
            return response()->json(['message' => "Sukses", 'data' => $jadwal], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal", 'error' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // UBAH DI SINI: jadwal_spmi
            'id'           => 'required|exists:jadwal_spmi,id',
            'nama_jadwal'  => 'required|max:255',
            'area_audit'   => 'required|max:255',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir'=> 'required|date|after_or_equal:tanggal_awal',
            'semester'     => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $id          = $request->input('id');
        $nama_jadwal = $request->input('nama_jadwal');
        $area_audit  = $request->input('area_audit');
        $tanggal_awal= $request->input('tanggal_awal');
        $tanggal_akhir=$request->input('tanggal_akhir');
        $semester    = $request->input('semester');

        $checkData = JadwalAudit::where('id', '=', $id)->first();
        if (!$checkData) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Sama seperti store() - nama jadwal boleh sama, cuma kombinasi nama_jadwal + area_audit
        // yang nggak boleh nabrak jadwal LAIN (di luar baris yang sedang diedit ini sendiri).
        $checkDuplikat = DB::table('jadwal_spmi')
            ->where('id', '!=', $id)
            ->where('nama_jadwal', $nama_jadwal)
            ->where('area_audit', $area_audit)
            ->first();
        if ($checkDuplikat) {
            return response()->json(['error' => 'Jadwal dengan nama & area audit yang sama sudah ada'], 400);
        }

        try {
            DB::beginTransaction();

            JadwalAudit::where('id', '=', $id)->update([
                'nama_jadwal'  => $nama_jadwal,
                'area_audit'   => $area_audit,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir'=> $tanggal_akhir,
                'semester'     => $semester,
            ]);

            DB::commit();
            return response()->json(['message' => "Sukses"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // UBAH DI SINI: jadwal_spmi
            'id' => 'required|exists:jadwal_spmi,id', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $id = $request->input('id');
        
        $checkData = JadwalAudit::where('id', '=', $id)->first();
        if (!$checkData) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        try {
            DB::beginTransaction();
            
            JadwalAudit::where('id', '=', $id)->delete();
            
            DB::commit();
            return response()->json(['message' => "Sukses"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal", 'error' => $e->getMessage()], 400);
        }
    }
}