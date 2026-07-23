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
]
export default auditeeRoute
