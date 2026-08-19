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
    path: '/auditor/instrumen-auditor/:id',
    name: 'InstrumenAuditor',
    component: () => import('@/views/pages/auth/auditor/IsiInstrumenAuditor.vue'),
    meta: {
      isAuditor: true,
      title: 'Isi Instrumen',
    },
    path: '/auditor/jadwal-auditor',
    name: 'AuditorJadwalAudit',
    component: () => import('@/views/pages/auth/auditor/JadwalAuditor.vue'),
    meta: {
      isAuditor: true,
      title: 'Jadwal Auditor',
    },
  },
]
export default auditorRoute
