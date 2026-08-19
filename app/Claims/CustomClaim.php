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
        
        if ($roleuser && $roleuser->role_id == 2) {
            
            // Kita join dosen_id ke tabel dosen, lalu ambil statusnya
            $datapenunjukan = DB::table('penunjukan_auditors as pa')
                ->join('dosen as d', 'pa.dosen_id', '=', 'd.id')
                ->where('d.user_id', $user_id)
                ->select('pa.status')
                ->first();
                
            if ($datapenunjukan) {
                // Kalau ketemu, timpa 'admin' jadi 'auditor' atau 'auditee'
                $rolename = $datapenunjukan->status;
            }
        }
        
        $token->addClaim('role_name', $rolename);
        $token->addClaim('semester', $semester->semester ?? '');
        $token->addClaim('login_name', $user->name);
        
        return $next($token); 
    }
}