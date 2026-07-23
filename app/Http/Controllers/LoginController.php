<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        

       ]);
        $name = $request->input('name');
        $check = \DB::table('userlogin')->where('name', $name)->first();
        if (!$check) {
            return response()->json(['message' => 'User/Password Salah'], 404);
        }
        $checkpassword = Hash::check($request->password, $check->password);
        if (!$checkpassword) {
            return response()->json(['message' => 'User/Password Salah'], 404);
        }
        $checksemesterberjalan = \DB::table('semester_berjalans')->select('semester')->first();
        if (!$checksemesterberjalan) {
            return response()->json(['message' => 'Semester belum dikumpulkan'], 404);
        }
        $checkroleuser = \DB::table('role_user')->where('user_id', $check->id)->select('role_id')->first();
        if (!$checkroleuser || $checkroleuser->role_id != 2) {
            return response()->json(['message' => 'Anda bukan dosen'], 404);
        }
        $checkdosen = \DB::table('dosen')->where('user_id', $check->id)->select('id')->first();
        if (!$checkdosen) {
            return response()->json(['message' => 'Dosen tidak ditemukan'], 404);
        }
        $checkpenunjukan = \DB::table('penunjukan_auditors')->where('dosen_id', $checkdosen->id)->where('semester', $checksemesterberjalan->semester)->select('status')->first();
        if (!$checkpenunjukan) { 
            return response()->json(['message' => 'Belum ada penunjukan auditor/auditee'], 404);    
        }     
         return response()->json(['message' => 'Login berhasil', 'data' => $check, 'semester' => $checksemesterberjalan->semester, 'role' => $checkpenunjukan->status, 'token' => $check->createToken('authToken')->accessToken], 200);  
    }

    public function logout(Request $request)
    {

        $removeToken = $request->user()->tokens()->delete();
        if ($removeToken) {

            return response()->json([
                'message' => 'Logout Success!',
            ], 200);
        }
    }

    public function changepassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:userlogin,id',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user_id = $request->input('user_id');
        $passwordlama = $request->input('old_password');
        $passwordbaru = $request->input('new_password');

        $checkpassword = Hash::check($passwordlama, \DB::table('userlogin')->where('id', $user_id)->value('password'));
        if (!$checkpassword) {
            return response()->json(['message' => 'Password lama salah'], 404); 
        }
        try {
           DB::beginTransaction();
            $Hashpasswordbaru = Hash::make($passwordbaru);
            \DB::table('userlogin')->where('id', $user_id)->update(['password' => $Hashpasswordbaru]);
            DB::commit();
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah password', 'error' => $e->getMessage()], 500);
        }
    }

    Public function Resetpassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:userlogin,id',
            
        ]);

        $user_id = $request->input('user_id');
  
        $userPasswordHash = \DB::table('userlogin')->where('id', $user_id)->first();
        if (!$userPasswordHash) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        

        try {
            \DB::beginTransaction();
            $hashBaru = Hash::make($userPasswordHash->name);
            \DB::table('userlogin')->where('id', $user_id)->update(['password' => $hashBaru]);
            \DB::commit();
            return response()->json(['message' => 'Reset password berhasil'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Gagal mereset password', 'error' => $e->getMessage()], 500);
        }
    }
               


    }
    

