<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuditeeController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter');
        $inputpaginate = $request->input('paginate');
        $inputlimit = $request->input('limit');
        $inputjadwalid = $request->input('params.jadwal_id') 
                      ?? $request->input('jadwal_spmi_id') 
                      ?? $request->input('jadwal_id');

        
        $query = DB::table('penunjukan_auditors as a')
            ->join('dosen as b' , 'a.dosen_id', '=', 'b.id')
            ->leftJoin('jadwal_spmi as j', 'a.jadwal_spmi_id', '=', 'j.id')
            ->select('a.id', 'b.nama_dosen', 'a.status', 'a.jadwal_spmi_id', 'j.nama_jadwal')
            ->where('a.status', 'auditee')
            ->when($filter, function ($query) use ($filter) {
                $query->where(function ($query) use ($filter) {
                    $query->where('b.nama_dosen', 'like', '%' . $filter . '%');
                });
            });

        if (!empty($inputjadwalid)) {
            $query->where('a.jadwal_spmi_id', $inputjadwalid);
        }

        $results = $inputpaginate === null
            ? ($inputlimit !== null ? $query->take($inputlimit)->get() : $query->get())
            : $query->paginate($inputpaginate);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        // 1. Validasi tanpa meminta 'status' dari Frontend
        $validator = Validator::make($request->all(), [
            'dosen_id' => 'required|exists:dosen,id',
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validatedData = $validator->validated();

        $validatedData['id'] = Str::uuid()->toString();

        $validatedData['status'] = 'auditee';

        $existingAuditee = DB::table('penunjukan_auditors')
            ->where('dosen_id', $validatedData['dosen_id'])
            ->where('jadwal_spmi_id', $validatedData['jadwal_spmi_id'])
            ->where('status', 'auditee')
            ->first();

        if ($existingAuditee) {
            return response()->json(['error' => 'Dosen sudah ditugaskan sebagai auditee pada jadwal ini.'], 400);
        }

        DB::table('penunjukan_auditors')->insert($validatedData);

        return response()->json(['message' => 'Auditee created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'dosen_id' => 'required|exists:dosen,id',
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
            'status' => 'required|in:auditor,auditee',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validatedData = $validator->validated();

        $existingAuditee = DB::table('penunjukan_auditors')
            ->where('id', '!=', $id)
            ->where('dosen_id', $validatedData['dosen_id'])
            ->where('jadwal_spmi_id', $validatedData['jadwal_spmi_id'])
            ->where('status', $validatedData['status'])
            ->first();

        if ($existingAuditee) {
            return response()->json(['error' => 'Dosen sudah ditugaskan sebagai ' . $validatedData['status'] . ' pada jadwal ini.'], 400);
        }

        DB::table('penunjukan_auditors')->where('id', $id)->update($validatedData);

        return response()->json(['message' => 'Auditee updated successfully.'], 200);
    }

    public function destroy($id)
    {
        DB::table('penunjukan_auditors')->where('id', $id)->delete();

        return response()->json(['message' => 'Auditee deleted successfully.'], 200);
    }

    /**
     * Jadwal audit di mana user yang sedang login ditugaskan sebagai Auditee.
     * Dipanggil dari JadwalAuditee.vue (GET /auditee/jadwal-saya).
     * Pola sama persis kayak AuditorController::jadwalSaya().
     */
    public function jadwalSaya(Request $request)
    {
        $user = $request->user();

        $dosen = DB::table('dosen')->where('user_id', $user->id)->first();
        if (!$dosen) {
            return response()->json(['error' => 'Akun ini tidak terdaftar sebagai dosen.'], 404);
        }

        $results = DB::table('penunjukan_auditors as pa')
            ->join('jadwal_spmi as j', 'pa.jadwal_spmi_id', '=', 'j.id')
            ->where('pa.dosen_id', $dosen->id)
            ->where('pa.status', 'auditee')
            ->select(
                'pa.id as penunjukan_id',
                'pa.jadwal_spmi_id',
                'j.nama_jadwal',
                'j.area_audit',
                'j.tanggal_awal',
                'j.tanggal_akhir',
                'j.semester'
            )
            ->orderBy('j.tanggal_awal', 'desc')
            ->get();

        return response()->json($results);
    }
}