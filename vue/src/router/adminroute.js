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
]

export default adminRoute
