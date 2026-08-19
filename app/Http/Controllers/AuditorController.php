<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
class AuditorController extends Controller
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
            ->select('a.id', 'b.nama_dosen', 'a.status', 'a.jadwal_spmi_id')
            ->where('a.status', 'auditor')
            ->when($filter, function ($query) use ($filter) {
                $query->where(function ($query) use ($filter) {
                    $query->where('b.nama_dosen', 'like', '%' . $filter . '%')
                        ->orWhere('a.status', 'like', '%' . $filter . '%'); 
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
        $validator = Validator::make($request->all(), [
            'dosen_id' => 'required|exists:dosen,id',
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validatedData = $validator->validated();

        $validatedData['id'] = Str::uuid()->toString();

        $validatedData['status'] = 'auditor';

        $existingAuditor = DB::table('penunjukan_auditors')
            ->where('dosen_id', $validatedData['dosen_id'])
            ->where('jadwal_spmi_id', $validatedData['jadwal_spmi_id'])
            ->where('status', 'auditor')
            ->first();

        if ($existingAuditor) {
            return response()->json(['error' => 'Dosen sudah ditugaskan sebagai auditor pada jadwal ini.'], 400);
        }

        DB::table('penunjukan_auditors')->insert($validatedData);

        return response()->json(['message' => 'Data berhasil ditambahkan.'], 201);
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

        $existingAuditor = DB::table('penunjukan_auditors')
            ->where('id', '!=', $id)
            ->where('dosen_id', $validatedData['dosen_id'])
            ->where('jadwal_spmi_id', $validatedData['jadwal_spmi_id'])
            ->where('status', $validatedData['status'])
            ->first();

        if ($existingAuditor) {
            return response()->json(['error' => 'Dosen sudah ditugaskan sebagai ' . $validatedData['status'] . ' pada jadwal ini.'], 400);
        }

        DB::table('penunjukan_auditors')->where('id', $id)->update($validatedData);

        return response()->json(['message' => 'Data berhasil diupdate.'], 200);
    }

    public function destroy($id)
    {
        DB::table('penunjukan_auditors')->where('id', $id)->delete();

        return response()->json(['message' => 'Data berhasil dihapus.'], 200);
    }
}