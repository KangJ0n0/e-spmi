<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ringkasan angka buat stat card di Dashboard Admin.
     * Dipanggil dari AdminHome.vue (GET /dashboard/stats).
     */
    public function stats()
    {
        return response()->json([
            'jadwal_audit' => DB::table('jadwal_spmi')->count(),
            'struktur_anggota' => DB::table('struktur_anggota')->count(),
            'dosen' => DB::table('dosen')->count(),
        ]);
    }
}
