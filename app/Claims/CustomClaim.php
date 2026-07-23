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
        $semester=DB::table('semester_berjalans')->select('semester')->first();
        $roleuser=DB::table('role_user')->where('user_id',$user_id)->select('role_id')->first();
        $rolename='admin';
        if($roleuser==2){
            $datapenunjukan=DB::table('penunjukan_auditors as pa')->join('dosen as d','pa.dosen_id','=','d.id')->
            where('d.user_id',$user_id)->where('pa.semester',$semester->semester)->select('pa.status')->first();
            if($datapenunjukan){
                if($datapenunjukan->status=='auditee'){
                    $rolename='auditee';
                }else{
                    $rolename='auditor';
                }
            }
        }
        $token->addClaim('role', $rolename);
        $token->addClaim('semester', $semester->semester);
        $token->addClaim('login_name' , $user->name);
        return $next($token); 
    }
}
