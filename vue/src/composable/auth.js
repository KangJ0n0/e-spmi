import axiosClient from '@/axios'
import router from '@/router'
import { jwtDecode } from 'jwt-decode'



export default function useAuth() {
 const login = async (data) => {
    try {
        console.log('Login data:', data)
      const response = await axiosClient.post('/login', data)
        console.log('Login response:', response)
      if (!response.response) {
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('refresh_token', response.data.refresh_token)
        const token = jwtDecode(response.data.token)
        const userrole = token.role_name

        // Define a mapping of roles to dashboard routes
        const roleDashboardMap = {
          admin: 'AdminDashboard',
          auditor: 'AuditorDashboard',
          auditee: 'AuditeeDashboard',

        
        }

        const dashboardName = roleDashboardMap[userrole]
        if (dashboardName) {
          await router.push({ name: dashboardName })
        }
        return { login_data: response.response }
      }
    } catch (error) {
      console.error('Login error:', error)
    }
  }
 
const logout = async () => {
    await axiosClient.post('/logout')
    localStorage.removeItem('token')
  
    router.replace({ name: 'Login' }).then(() => {
      setTimeout(() => {
        location.reload()
      }, 2000)
    })
  }
  
  const ChangePassword = async (data) => {
    try {
      const response = await axiosCLient.post('/change-password', data)
      return { error: response.response }
    } catch (error) {
      return error.response
    }
  }
  const ResetPassword = async (data) => {
    try {
      const response = await axiosCLient.post('/reset-password', data)
      return { error: response.response }
    } catch (error) {
      return error.response
    }
  }
  
  return {
    
    login,
    logout,
    ChangePassword,
    ResetPassword,
    
  }
}