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
          <p
            v-if="statsLoading"
            class="text-3xl font-bold text-gray-300 animate-pulse"
          >
            --
          </p>
          <p v-else class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
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

import { ref, onMounted } from 'vue'
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

const stats = ref([
  { key: 'jadwal_audit', label: 'Jadwal Audit', value: 0, note: 'total jadwal terdaftar' },
  { key: 'struktur_anggota', label: 'Struktur Anggota LPMU', value: 0, note: 'total anggota' },
  { key: 'dosen', label: 'Data Dosen (SIAKAD)', value: 0, note: 'sinkronisasi ETL' },
])

// Mulai true supaya kartu nampilin placeholder "--" dulu, bukan langsung "0" yang keliatan
// kayak data asli - "0" cuma dihapus begitu respons /dashboard/stats beneran balik.
const statsLoading = ref(true)

async function getStats() {
  statsLoading.value = true
  try {
    const response = await axiosClient.get('/dashboard/stats')
    stats.value = stats.value.map((stat) => ({
      ...stat,
      value: response.data?.[stat.key] ?? 0,
    }))
  } catch (error) {
    console.error('Gagal memuat ringkasan dashboard:', error)
  } finally {
    statsLoading.value = false
  }
}

onMounted(() => {
  getStats()
})

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
    icon: '🧑‍⚖️',
    label: 'Auditor/Auditee',
    description: 'Lihat penugasan Auditor & Auditee',
    href: '/admin/auditor-auditee',
  },
  {
    icon: '📝',
    label: 'Kuisioner',
    description: 'Kelola kuisioner audit',
    href: '/admin/kuisioner',
  },
  {
    icon: '🗂️',
    label: 'Bank Pertanyaan',
    description: 'Kelola bank pertanyaan instrumen',
    href: '/admin/bank-pertanyaan',
  },
  {
    icon: '🖨️',
    label: 'Cetak Dokumen',
    description: 'Cetak dokumen instrumen audit',
    href: '/admin/cetak-dokumen',
  },
  {
    icon: '👤',
    label: 'Kelola Pengguna',
    description: 'Kelola akun Auditor & Auditee',
    href: '/admin/users',
  },
])
</script>