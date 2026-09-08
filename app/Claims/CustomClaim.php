<?php

namespace App\Claims;

use CorBosman\Passport\AccessToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomClaim
{
    public function handle(AccessToken $token, $next)
    {
        $user = User::find($token->getUserIdentifier());
        $user_id = $user->id;
        
        $semester = DB::table('semester_berjalans')->select('semester')->first();
        $roleuser = DB::table('role_user')->where('user_id', $user_id)->select('role_id')->first();
        
        // Default aman biar Vue lu nggak blank putih
        $rolename = 'admin';
        // Nama dosen (buat ditampilin di pojok kanan atas layout, gantiin "Pengguna").
        // Admin nggak punya baris di tabel dosen, jadi ini tetap null buat Admin - frontend
        // fallback ke login_name (username) kalau nama_dosen null.
        $namadosen = null;

        if ($roleuser && $roleuser->role_id == 2) {

            // Kita join dosen_id ke tabel dosen, lalu ambil statusnya + namanya
            $datapenunjukan = DB::table('penunjukan_auditors as pa')
                ->join('dosen as d', 'pa.dosen_id', '=', 'd.id')
                ->where('d.user_id', $user_id)
                ->select('pa.status', 'd.nama_dosen')
                ->first();

            if ($datapenunjukan) {
                // Kalau ketemu, timpa 'admin' jadi 'auditor' atau 'auditee'
                $rolename = $datapenunjukan->status;
                $namadosen = $datapenunjukan->nama_dosen;
            }
        }

        $token->addClaim('role_name', $rolename);
        $token->addClaim('semester', $semester->semester ?? '');
        $token->addClaim('login_name', $user->name);
        $token->addClaim('nama_dosen', $namadosen);
        
        return $next($token); 
    }
}