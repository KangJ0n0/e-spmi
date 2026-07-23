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
      component: () => import('@/views/layout/MainLayout.vue'),
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
})

const getToken = () => {
  return localStorage.getItem('token')
}

const isAuthenticated = () => {
  return getToken() !== null
}

const roleHomeMap = {
  admin: 'AdminDashboard',
  auditor: 'AuditorDashboard',
  auditee: 'AuditeeDashboard',
}

const isTokenExpired = () => {
  const token = getToken()
  if (!token) {
    return true
  }
  const decodeToken = jwtDecode(token)
  return decodeToken.exp < Date.now() / 1000
}

const handleAuth = async (to, from, next) => {
  const token = getToken()
  const role_name = token ? jwtDecode(token).role_name : null
  if (!token) {
    if (to.meta.requiresAuth) {
      return next('/login')
    }
    return next()
  }
  if (isTokenExpired()) {
    localStorage.removeItem('token')
    if (to.meta.requiresAuth) {
      return next({ path: '/login' })
    }
    return next()
  }

  if (isAuthenticated()) {
    if (to.meta.requiresAuth === false) {
      if (to.name === 'Login') {
        return next({ name: roleHomeMap[role_name] })
      } else {
        return next()
      }
    } else {
      const roleChecks = [{ condition: to.meta.isAdmin, roles: ['admin_bima'] }]
      for (const check of roleChecks) {
        if (check.condition && check.roles.includes(role_name)) {
          next()
          return
        }
      }
      return next(roleHomeMap[role_name] ? { name: roleHomeMap[role_name] } : '/')
    }
  } else {
    next()
  }

  router.beforeEach((to, from, next) => {
    handleAuth(to, from, next)
  })
}
export default router
