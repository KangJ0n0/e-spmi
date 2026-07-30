const guestRoute = [
{
    path: '/',
    name: 'Home',
    component: () => import('@/views/pages/guest/LandingPage.vue'),
    meta: {
      isGuest: true,
    }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/pages/guest/Login.vue'),
    meta: {
      isGuest: true,
    },
  },
{
  
    path: '/profil',
    component: () => import('@/views/pages/guest/Profil.vue'),
    meta: { isGuest: true },
    children: [
      {
        path: '',
        redirect: { name: 'ProfilVisi' },
      },
      {
        path: 'visi',
        name: 'ProfilVisi',
        component: () => import('@/views/pages/guest/profil/Visi.vue'),
        meta: { isGuest: true },
      },
      {
        path: 'UPMF',
        name: 'ProfilUMPF',
        component: () => import('@/views/pages/guest/profil/Lpmf.vue'),
        meta: { isGuest: true },
      },
      {
        path: 'struktur-organisasi',
        name: 'ProfilStrukturOrganisasi',
        component: () => import('@/views/pages/guest/profil/StrukturOrganisasi.vue'),
        meta: { isGuest: true },
      },
      {
        path: 'tugas-fungsi',
        name: 'ProfilTugasFungsi',
        component: () => import('@/views/pages/guest/profil/TugasFungsi.vue'),
        meta: { isGuest: true },
      },
    ],
  },
  {
    path: '/spme',
    name: 'SPME',
    component: () => import('@/views/pages/guest/Spme.vue'),
    meta: {
      isGuest: true,
    },
  },
  {
    path: '/spmi',
    name: 'SPMI',
    component: () => import('@/views/pages/guest/Spmi.vue'),
    meta: {
      isGuest: true,
    },
    children: [
      {
        path: '',
        redirect: { name: 'Penetapan' },
      },
      {
        path: 'penetapan',
        name: 'Penetapan',
        component: () => import('@/views/pages/guest/spmi/Penetapan.vue'),
        meta: { isGuest: true },
      }
    ],
  },
  {
    path: '/kuisioner',
    name: 'Kuisioner',
    component: () => import('@/views/pages/guest/Kuisioner.vue'),
    meta: {
      isGuest: true,
    },
  }
];
export default guestRoute
