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
    path: '/auditee/jadwal-auditee',
    name: 'AuditeeJadwalAudit',
    component: () => import('@/views/pages/auth/auditee/JadwalAuditee.vue'),
    meta: {
      isAuditee: true,
      title: 'Jadwal Auditee',
    },
  },
]
export default auditeeRoute
