<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\JadwalAudit;
use App\Imports\JadwalAuditImport;
use Maatwebsite\Excel\Facades\Excel;

class JadwalAuditController extends Controller
{
    // Import Jadwal Audit dari Excel (13 Sep 2026) - Admin upload banyak baris Jadwal Audit
    // sekaligus, dibanding input manual satu-satu. Pola respons SAMA PERSIS dengan
    // BankPertanyaanController::importExcel() (lihat JadwalAuditImport.php buat detail aturan
    // per-kolom & baris yang dilewati).
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new JadwalAuditImport();
            Excel::import($import, $request->file('file'));

            $peringatan = array_values(array_filter($import->skipped, fn($s) => $s['tipe'] === 'peringatan'));
            $info = array_values(array_filter($import->skipped, fn($s) => $s['tipe'] === 'info'));

            if ($import->importedCount === 0) {
                return response()->json([
                    'message'     => 'Import selesai, namun 0 jadwal berhasil masuk. Pastikan judul kolom Excel (nama_jadwal, area_audit, tanggal_awal, tanggal_akhir, semester) ada di baris pertama!',
                    'total_baris' => $import->totalBaris,
                    'berhasil'    => 0,
                    'peringatan'  => $peringatan,
                    'info'        => $info,
                ], 400);
            }

            return response()->json([
                'message'     => 'Berhasil import ' . $import->importedCount . ' Jadwal Audit ke database!',
                'total_baris' => $import->totalBaris,
                'berhasil'    => $import->importedCount,
                'peringatan'  => $peringatan,
                'info'        => $info,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal import data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

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
        // adalah kombinasi nama_jadwal + area_audit + semester (itu baru dianggap jadwal
        // duplikat). Sebelumnya cek ini cuma lihat nama_jadwal sendirian, jadi nama yang sama
        // sekalipun area audit-nya beda (mis. "AMI" - Fakultas Ekonomi vs "AMI" - Fakultas
        // Teknik) ikut keblokir - padahal itu 2 jadwal yang sah beda.
        //
        // FIX (13 Sep 2026): `semester` BELUM ikut di cek ini - efeknya Admin nggak bisa bikin
        // jadwal baru buat semester berikutnya kalau nama & area audit-nya sama kayak jadwal
        // semester sebelumnya (mis. "Audit Mutu Internal Teknik" - Fakultas Teknik dipakai tiap
        // semester), padahal itu memang wajar & harus boleh - yang beneran duplikat cuma kalau
        // nama + area audit + SEMESTER-nya sama persis (2 baris identik di semester yang sama).
        $checkData = DB::table('jadwal_spmi')
            ->where('nama_jadwal', $nama_jadwal)
            ->where('area_audit', $area_audit)
            ->where('semester', $semester)
            ->first();
        if ($checkData) {
            return response()->json(['error' => 'Jadwal dengan nama, area audit, dan semester yang sama sudah ada'], 400);
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
        // + semester yang nggak boleh nabrak jadwal LAIN (di luar baris yang sedang diedit ini
        // sendiri). Lihat catatan FIX (13 Sep 2026) di store() - semester WAJIB ikut dicek biar
        // Admin tetap bisa pindahin/edit jadwal ke semester lain tanpa keblokir gara-gara nama &
        // area audit sama kayak jadwal semester lain yang memang sah beda.
        $checkDuplikat = DB::table('jadwal_spmi')
            ->where('id', '!=', $id)
            ->where('nama_jadwal', $nama_jadwal)
            ->where('area_audit', $area_audit)
            ->where('semester', $semester)
            ->first();
        if ($checkDuplikat) {
            return response()->json(['error' => 'Jadwal dengan nama, area audit, dan semester yang sama sudah ada'], 400);
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

            // FIX (12 Sep): `penunjukan_auditors.jadwal_spmi_id` TIDAK PERNAH punya foreign key
            // constraint (dicek langsung di migration-nya - beda dengan `list_pertanyaans` yang
            // sudah benar pakai ->onDelete('cascade')). Efeknya, hapus jadwal di sini SELAMA INI
            // ninggalin baris penugasan Auditor/Auditee "nyantol" (orphan) di penunjukan_auditors,
            // dan baris orphan itu ikut muncul di halaman Admin > Auditor/Auditee (leftJoin di
            // AuditorController/AuditeeController::index() nampilin baris meski jadwal_spmi-nya
            // sudah nggak ada). Baris ini nutup celahnya di level aplikasi.
            DB::table('penunjukan_auditors')->where('jadwal_spmi_id', $id)->delete();

            JadwalAudit::where('id', '=', $id)->delete();

            DB::commit();
            return response()->json(['message' => "Sukses"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal", 'error' => $e->getMessage()], 400);
        }
    }
}