const adminRoute = [
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: () => import('@/views/pages/auth/admin/AdminHome.vue'),
    meta: {
      isAdmin: true,
      title: 'Dashboard',
    },
  },
  {
    path: '/admin/struktur-anggota',
    name: 'StrukturAnggota',
    component: () => import('@/views/pages/auth/admin/StrukturAnggota.vue'),
    meta: {
      isAdmin: true,
      title: 'StrukturAnggota',
    },
  },
  {
    path: '/admin/jadwal-audit',
    name: 'AdminJadwalAudit',
    component: () => import('@/views/pages/auth/admin/JadwalAudit.vue'),
    meta: {
      isAdmin: true,
      title: 'Jadwal Audit',
    },
  },
  {
    path: '/admin/kuisioner',
    name: 'AdminKelolaKuisioner',
    component: () => import('@/views/pages/auth/admin/KelolaKuisioner.vue'),
    meta: { isAdmin: true, requiresAuth: true, title: 'Kelola Kuisioner' },
  },
  {
    path: '/admin/bank-pertanyaan',
    name: 'AdminBankPertanyaan',
    component: () => import('@/views/pages/auth/admin/BankPertanyaan.vue'),
    meta: { isAdmin: true, requiresAuth: true, title: 'Bank Pertanyaan' },
  },
  {
    // Halaman BARU: cetak dokumen resmi Instrumen 1-4 (PDF). File komponennya di folder
    // `shared/` (bukan `admin/`) karena route yang sama dipakai juga oleh Auditor
    // (lihat auditorroute.js) - komponennya baca route.meta.isAdmin buat nentuin sumber data
    // jadwal yang mana yang dipanggil.
    path: '/admin/cetak-dokumen',
    name: 'AdminCetakDokumen',
    component: () => import('@/views/pages/auth/shared/CetakDokumen.vue'),
    meta: { isAdmin: true, requiresAuth: true, title: 'Cetak Dokumen' },
  },
]

export default adminRoute
