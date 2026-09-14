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

        // Fix bug (14 Sep 2026, ditemukan pas testing E2E) - route POST /auditor/data dibuka juga
        // buat Auditor & Auditee (dulu admin-only), soalnya AuditeeHome.vue perlu manggil endpoint
        // ini (dengan filter jadwal_id) buat nampilin nama Auditor yang ditugaskan di dashboard-nya
        // sendiri. Makanya di sini WAJIB ada pengecekan otorisasi manual, non-Admin cuma boleh:
        // (a) query PAKAI filter jadwal_id, DAN (b) dirinya sendiri emang ditugaskan (auditor ATAU
        // auditee, status apapun) di jadwal itu. Tanpa ini, sembarang Auditor/Auditee bisa intip
        // daftar auditor jadwal siapapun, atau bahkan daftar GLOBAL semua jadwal (cabang di bawah).
        $user = $request->user();
        $dosen = DB::table('dosen')->where('user_id', $user->id)->first();
        $isAdmin = !$dosen;

        if (!$isAdmin && empty($inputjadwalid)) {
            // Non-Admin coba akses daftar GLOBAL (tanpa filter jadwal) - selalu ditolak, cabang
            // ini cuma buat halaman Admin > Auditor/Auditee (AuditorAuditeeAdmin.vue).
            return response()->json(['error' => 'Anda tidak berhak mengakses data ini.'], 403);
        }

        if (!$isAdmin && !empty($inputjadwalid)) {
            $ditugaskan = DB::table('penunjukan_auditors')
                ->where('jadwal_spmi_id', $inputjadwalid)
                ->where('dosen_id', $dosen->id)
                ->exists();

            if (!$ditugaskan) {
                return response()->json(['error' => 'Anda tidak ditugaskan pada jadwal ini.'], 403);
            }
        }

        // Ada filter jadwal spesifik (panel Penugasan di JadwalAuditForm.vue, atau
        // AuditeeHome.vue) - perilaku LAMA dipertahankan PERSIS: 1 baris flat per penugasan.
        if (!empty($inputjadwalid)) {
            $query = DB::table('penunjukan_auditors as a')
                ->join('dosen as b' , 'a.dosen_id', '=', 'b.id')
                ->leftJoin('jadwal_spmi as j', 'a.jadwal_spmi_id', '=', 'j.id')
                // + a.is_ketua (8 Sep) - ditambah select-nya + jadi urutan utama, biar Ketua Auditor
                // selalu tampil PALING ATAS di panel Penugasan Auditor (JadwalAuditForm.vue), sama
                // urutan yang dipakai pas cetak dokumen (lihat DokumenAuditController::generate()).
                ->select('a.id', 'b.nama_dosen', 'a.status', 'a.jadwal_spmi_id', 'j.nama_jadwal', 'a.is_ketua')
                ->where('a.status', 'auditor')
                ->when($filter, function ($query) use ($filter) {
                    $query->where(function ($query) use ($filter) {
                        $query->where('b.nama_dosen', 'like', '%' . $filter . '%')
                            ->orWhere('a.status', 'like', '%' . $filter . '%');
                    });
                })
                ->orderByDesc('a.is_ketua')
                ->where('a.jadwal_spmi_id', $inputjadwalid);

            $results = $inputpaginate === null
                ? ($inputlimit !== null ? $query->take($inputlimit)->get() : $query->get())
                : $query->paginate($inputpaginate);

            return response()->json($results);
        }

        // TANPA filter jadwal - halaman Admin > Auditor/Auditee (AuditorAuditeeAdmin.vue),
        // daftar GLOBAL semua jadwal. Digabung 1 baris per dosen (12 Sep, permintaan user: nama
        // dosen yang pegang banyak jadwal dulu muncul berulang-ulang di daftar flat) - tiap
        // baris dosen bawa `jadwal_list` (semua jadwal tempat dia jadi auditor).
        //
        // INNER JOIN ke jadwal_spmi (bukan leftJoin kayak cabang di atas) sengaja dipakai di sini
        // sebagai lapis pertahanan KEDUA terhadap baris "orphan" (penugasan yang jadwalnya sudah
        // dihapus) - lapis pertama ada di JadwalAuditController::destroy() (cascade delete) +
        // migration cleanup data lama. Kalau karena suatu hal masih ada baris orphan yang lolos,
        // inner join otomatis nyaring, nggak ikut nongol di sini.
        $rows = DB::table('penunjukan_auditors as a')
            ->join('dosen as b', 'a.dosen_id', '=', 'b.id')
            ->join('jadwal_spmi as j', 'a.jadwal_spmi_id', '=', 'j.id')
            ->select(
                'a.id', 'a.dosen_id', 'b.nama_dosen', 'a.jadwal_spmi_id',
                'j.nama_jadwal', 'j.semester', 'j.tanggal_awal', 'a.is_ketua'
            )
            ->where('a.status', 'auditor')
            ->when($filter, function ($query) use ($filter) {
                $query->where('b.nama_dosen', 'like', '%' . $filter . '%');
            })
            ->orderBy('b.nama_dosen')
            ->orderByDesc('j.tanggal_awal')
            ->get();

        $grouped = $rows->groupBy('dosen_id')->map(function ($items) {
            $first = $items->first();
            return [
                'dosen_id' => $first->dosen_id,
                'nama_dosen' => $first->nama_dosen,
                'jumlah_jadwal' => $items->count(),
                'jadwal_list' => $items->map(function ($item) {
                    return [
                        'penugasan_id' => $item->id,
                        'jadwal_spmi_id' => $item->jadwal_spmi_id,
                        'nama_jadwal' => $item->nama_jadwal,
                        'semester' => $item->semester,
                        'is_ketua' => (bool) $item->is_ketua,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json($grouped);
    }

    /**
     * Tandai 1 auditor sebagai "Ketua Auditor" untuk jadwal terkait (dipanggil dari tombol
     * mahkota/bintang di panel Penugasan Auditor, JadwalAuditForm.vue). Cuma boleh ada 1 Ketua
     * per jadwal - baris lain di jadwal yang sama otomatis dilepas status Ketua-nya dulu.
     * Dipakai DokumenAuditController::generate() buat nentuin siapa yang muncul di kolom tanda
     * tangan (footer) + siapa yang ditaruh paling atas di daftar bernomor AUDITOR (header).
     */
    public function setKetua($id)
    {
        $row = DB::table('penunjukan_auditors')
            ->where('id', $id)
            ->where('status', 'auditor')
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Data auditor tidak ditemukan.'], 404);
        }

        try {
            DB::beginTransaction();

            DB::table('penunjukan_auditors')
                ->where('jadwal_spmi_id', $row->jadwal_spmi_id)
                ->where('status', 'auditor')
                ->update(['is_ketua' => false]);

            DB::table('penunjukan_auditors')->where('id', $id)->update(['is_ketua' => true]);

            DB::commit();
            return response()->json(['message' => 'Ketua Auditor berhasil ditentukan.'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal', 'error' => $e->getMessage()], 400);
        }
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

        // Auditor PERTAMA yang ditugaskan di 1 jadwal otomatis jadi Ketua Auditor (11 Sep 2026,
        // permintaan user) - biar nggak perlu klik ikon mahkota manual lagi buat kasus paling
        // umum (baru mulai isi jadwal, belum ada auditor sama sekali). Auditor ke-2/dst tetap
        // masuk sebagai anggota biasa - Admin masih bisa pindah Ketua manual lewat setKetua().
        $sudahAdaAuditor = DB::table('penunjukan_auditors')
            ->where('jadwal_spmi_id', $validatedData['jadwal_spmi_id'])
            ->where('status', 'auditor')
            ->exists();
        $validatedData['is_ketua'] = !$sudahAdaAuditor;

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

    /**
     * Jadwal audit di mana user yang sedang login ditugaskan sebagai Auditor.
     * Dipanggil dari JadwalAuditor.vue (GET /auditor/jadwal-saya).
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
            ->where('pa.status', 'auditor')
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