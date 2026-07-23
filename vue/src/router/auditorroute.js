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
]
export default auditorRoute
