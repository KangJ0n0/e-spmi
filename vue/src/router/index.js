import { createRouter, createWebHistory } from 'vue-router'

// Fallback empty adminRoute to avoid undefined reference when no admin routes are defined

import { jwtDecode } from 'jwt-decode'
import axiosClient from '@/axios'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import auditorRoute from './auditorroute'
import auditeeRoute from './auditeeroute'
import guestRoute from './guestroute'
import adminRoute from './adminroute'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: () => import('@/views/layout/GuestLayout.vue'),
      children: [...guestRoute],
      meta: { requiresAuth: false },
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/pages/guest/Login.vue'),
      meta: { requiresAuth: false },
    },

    {
      path: '/auth',
      redirect: '/auth',
      meta: { requiresAuth: true },
      component: () => import('@/views/layout/MainLayout.vue'),
      children: [...adminRoute, ...auditorRoute, ...auditeeRoute],
    },

    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: () => import('@/views/pages/error/404.vue'),
      meta: { requiresAuth: false },
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    return { top: 0 }
  },
})

const getToken = () => localStorage.getItem('token')
const isAuthenticated = () => !!getToken()
const roleDashboardMap = {
  admin: 'AdminDashboard',
  auditor: 'AuditorDashboard',
  auditee: 'AuditeeDashboard',
 
}

const isTokenExpired = (token) => {
  const decodedToken = jwtDecode(token)
  return decodedToken.exp < Date.now() / 1000
}
const handleAuthentication = (to, next) => {
  const token = getToken()

  if (token && isTokenExpired(token)) {
    localStorage.removeItem('token')
    next('/login')
    return
  }

  if (to.meta.isGuest) {
    next()
    return
  }

  if (to.meta.requiresAuth && !isAuthenticated()) {
    next({ path: '/login' })
    return
  }

  if (isAuthenticated()) {
    const role_name = jwtDecode(token).role_name

    // Check if user role matches the required role for the route
    const roleChecks = [
      { condition: to.meta.isAdmin, roles: ['admin'] },
      { condition: to.meta.isAuditor, roles: ['auditor'] },
      { condition: to.meta.isAuditee, roles: ['auditee'] },
     
    ]

    for (const check of roleChecks) {
      if (check.condition && check.roles.includes(role_name)) {
        next()
        return
      }
    }

    // Default action if no specific route is matched
    next(roleDashboardMap[role_name] ? { name: roleDashboardMap[role_name] } : { name: 'Login' })
  } else {
    next()
  }
}

router.beforeEach((to, from, next) => {
  handleAuthentication(to, next)
})
export default router
