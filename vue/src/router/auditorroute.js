const auditorRoute = [
  {
    path: '/auditor',
    name: 'AuditorDashboard',
    component: () => import('@/views/pages/auth/auditor/AuditorHome.vue'),
    meta: {
      isAuditor: true,
      title: 'Dashboard',
    },
  },
  {
    // NOTE: sebelumnya route ini nempel jadi satu object sama '/auditor/jadwal-auditor' di
    // bawahnya (duplicate key di object literal), jadi ketimpa dan nggak pernah kedaftar
    // (404 kalau diakses). Sekarang dipisah jadi object masing-masing.
    // CATATAN: route + komponen IsiInstrumenAuditor.vue ini SENGAJA dibiarkan (dead code, tidak
    // ada link yang mengarah ke sini lagi) sesuai preferensi user - halaman aktifnya sekarang
    // NilaiInstrumenAuditor.vue di route /auditor/nilai-instrumen/:id di bawah.
    path: '/auditor/instrumen-auditor/:id',
    name: 'InstrumenAuditor',
    component: () => import('@/views/pages/auth/auditor/IsiInstrumenAuditor.vue'),
    meta: {
      isAuditor: true,
      title: 'Isi Instrumen',
    },
  },
  {
    // Halaman BARU (terpisah dari IsiInstrumenAuditor.vue): lihat jawaban Auditee, tentukan
    // KS/KTS, lengkapi Instrumen 3-6. Ini yang dipakai tombol "Lihat Jawaban & Nilai" di
    // JadwalAuditor.vue sekarang.
    path: '/auditor/nilai-instrumen/:id',
    name: 'NilaiInstrumenAuditor',
    component: () => import('@/views/pages/auth/auditor/NilaiInstrumenAuditor.vue'),
    meta: {
      isAuditor: true,
      title: 'Nilai Instrumen',
    },
  },
  {
    path: '/auditor/jadwal-auditor',
    name: 'AuditorJadwalAudit',
    component: () => import('@/views/pages/auth/auditor/JadwalAuditor.vue'),
    meta: {
      isAuditor: true,
      title: 'Jadwal Auditor',
    },
  },
  {
    path: '/auditor/pilih-pertanyaan/:id',
    name: 'PilihPertanyaanAuditor',
    component: () => import('@/views/pages/auth/auditor/PilihPertanyaanAuditor.vue'),
    meta: {
      isAuditor: true,
      title: 'Pilih Pertanyaan',
    },
  },
  {
    // Halaman BARU (sama seperti AdminCetakDokumen di adminroute.js, komponen sama - lihat
    // catatan di sana): cetak dokumen resmi Instrumen 1-4 (PDF).
    path: '/auditor/cetak-dokumen',
    name: 'AuditorCetakDokumen',
    component: () => import('@/views/pages/auth/shared/CetakDokumen.vue'),
    meta: {
      isAuditor: true,
      title: 'Cetak Dokumen',
    },
  },
]
export default auditorRoute
