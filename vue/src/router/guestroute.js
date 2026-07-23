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
    name: 'Profil',
    component: () => import('@/views/pages/guest/Profil.vue'),
    meta: {
      isGuest: true,
    }
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
