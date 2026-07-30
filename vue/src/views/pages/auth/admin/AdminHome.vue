<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <div class="flex items-start justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 mb-1">Dashboard Admin LPMU</h1>
          <p class="text-gray-500">Ringkasan data E-SPMI</p>
        </div>
      </div>

      <!-- Ringkasan / Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="bg-white rounded-lg shadow-sm border border-gray-100 p-5"
        >
          <p class="text-sm text-gray-500 mb-1">{{ stat.label }}</p>
          <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
          <p v-if="stat.note" class="text-xs text-gray-400 mt-1">{{ stat.note }}</p>
        </div>
      </div>

      <!-- Menu Navigasi -->
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a
          v-for="menu in menus"
          :key="menu.label"
          :href="menu.href"
          class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-blue-200 transition flex flex-col items-start"
        >
          <span class="text-2xl mb-2">{{ menu.icon }}</span>
          <span class="font-medium text-gray-800">{{ menu.label }}</span>
          <span class="text-xs text-gray-400 mt-1">{{ menu.description }}</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'AdminDashboard' })

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axiosClient from '@/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()
const loggingOut = ref(false)

async function handleLogout() {
  loggingOut.value = true
  try {
    await axiosClient.post('/logout')
  } catch (error) {
    // Tetap lanjut logout di sisi FE meskipun request gagal (misal token sudah expired duluan)
    console.warn('Logout request gagal, tetap membersihkan sesi lokal:', error)
  } finally {
    localStorage.removeItem('token')
    toast.success('Berhasil logout')
    loggingOut.value = false
    router.push('/')
  }
}

// TODO: ganti dengan data dari API/props sesuai controller Admin LPMU
const stats = ref([
  { label: 'Jadwal Audit', value: 0, note: 'total jadwal terdaftar' },
  { label: 'Struktur Anggota LPMU', value: 0, note: 'total anggota' },
  { label: 'Data Dosen (SIAKAD)', value: 0, note: 'sinkronisasi ETL' },
])

const menus = ref([
  {
    icon: '📅',
    label: 'Jadwal Audit',
    description: 'Kelola jadwal audit internal',
    href: '/admin/jadwal-audit',
  },
  {
    icon: '🧑‍🤝‍🧑',
    label: 'Struktur Anggota',
    description: 'Kelola struktur anggota LPMU',
    href: '/admin/struktur-anggota',
  },
  {
    icon: '👤',
    label: 'Kelola Pengguna',
    description: 'Kelola akun Auditor & Auditee',
    href: '/admin/users',
  },
])
</script>