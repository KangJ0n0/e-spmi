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
]
export default adminRoute
