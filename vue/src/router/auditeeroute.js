const auditeeRoute = [
  {
    path: '/auditee',
    name: 'AuditeeDashboard',
    component: () => import('@/views/pages/auth/auditee/AuditeeHome.vue'),
    meta: {
      isAuditee: true,
      title: 'Dashboard',
    },
  },
  {
    path: '/auditee/instrumen-auditee',
    name: 'InstrumenAuditee',
    component: () => import('@/views/pages/auth/auditee/IsiInstrumenAuditee.vue'),
    meta: {
      isAuditee: true,
      title: 'Lihat Pertanyaan',
    },
  },
  {
    // CATATAN (11 Sep 2026): halaman gabungan lama, sudah tidak ditautkan dari sidebar (lihat
    // useSidebar.js) - digantikan 2 halaman terpisah di bawah (AuditeeEvaluasiDiriList &
    // AuditeeLihatHasilList) sesuai permintaan user ("terlalu banyak merangkum hal jadi satu,
    // sebaiknya jadi 2 halaman"). Route + komponen SENGAJA dibiarkan biar link/bookmark lama
    // tetap jalan.
    path: '/auditee/jadwal-auditee',
    name: 'AuditeeJadwalAudit',
    component: () => import('@/views/pages/auth/auditee/JadwalAuditee.vue'),
    meta: {
      isAuditee: true,
      title: 'Jadwal Auditee',
    },
  },
  // Halaman terpisah (10 Sep 2026) - sebelumnya "Lihat Hasil" numpuk di halaman yang sama dengan
  // form Jawab Pertanyaan (IsiInstrumenAuditee.vue, toggle state lokal). User minta dipisah biar
  // nggak bareng - dibuka dari tombol "Lihat Hasil" di kartu jadwal (JadwalAuditee.vue), sama
  // pola query-param `id` seperti /auditee/instrumen-auditee.
  {
    path: '/auditee/lihat-hasil',
    name: 'AuditeeLihatHasil',
    component: () => import('@/views/pages/auth/auditee/LihatHasilAuditee.vue'),
    meta: {
      isAuditee: true,
      title: 'Lihat Hasil',
    },
  },
  {
    // Halaman BARU (11 Sep 2026) - lihat EvaluasiDiriList.vue untuk detail.
    path: '/auditee/evaluasi-diri',
    name: 'AuditeeEvaluasiDiriList',
    component: () => import('@/views/pages/auth/auditee/EvaluasiDiriList.vue'),
    meta: {
      isAuditee: true,
      title: 'Evaluasi Diri',
    },
  },
  {
    // Halaman BARU (11 Sep 2026) - lihat LihatHasilList.vue untuk detail soal kenapa path-nya
    // /auditee/hasil-evaluasi (BUKAN /auditee/lihat-hasil, yang sudah dipakai halaman detail).
    path: '/auditee/hasil-evaluasi',
    name: 'AuditeeLihatHasilList',
    component: () => import('@/views/pages/auth/auditee/LihatHasilList.vue'),
    meta: {
      isAuditee: true,
      title: 'Lihat Hasil',
    },
  },
]
export default auditeeRoute
