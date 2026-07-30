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
    }
  },
]
export default adminRoute
