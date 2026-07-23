<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 mb-1">Dashboard Auditor</h1>
      <p class="text-gray-500 mb-8">Ringkasan tugas audit Anda</p>

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

      <!-- Jadwal Audit Mendatang -->
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Jadwal Audit Mendatang</h2>
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-10">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
              <th class="px-5 py-3 font-medium">Tanggal</th>
              <th class="px-5 py-3 font-medium">Unit Diaudit</th>
              <th class="px-5 py-3 font-medium">Status</th>
              <th class="px-5 py-3 font-medium"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="jadwal in jadwalAudit"
              :key="jadwal.id"
              class="border-t border-gray-100 hover:bg-gray-50"
            >
              <td class="px-5 py-3 text-gray-700">{{ jadwal.tanggal }}</td>
              <td class="px-5 py-3 text-gray-700">{{ jadwal.unit }}</td>
              <td class="px-5 py-3">
                <span
                  class="text-xs px-2 py-1 rounded-full font-medium"
                  :class="statusClass(jadwal.status)"
                >
                  {{ jadwal.status }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <a :href="`/auditor/audit/${jadwal.id}`" class="text-blue-600 hover:underline text-sm">
                  Detail
                </a>
              </td>
            </tr>
            <tr v-if="jadwalAudit.length === 0">
              <td colspan="4" class="px-5 py-6 text-center text-gray-400">
                Belum ada jadwal audit
              </td>
            </tr>
          </tbody>
        </table>
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
defineOptions({ name: 'AuditorDashboard' })

import { ref } from 'vue'

// TODO: ganti dengan data dari API/props sesuai controller Auditor
const stats = ref([
  { label: 'Jadwal Audit Saya', value: 0, note: 'audit yang ditugaskan' },
  { label: 'Audit Selesai', value: 0, note: 'sudah dilaporkan' },
  { label: 'Temuan Belum Ditindaklanjuti', value: 0, note: 'menunggu RTL auditee' },
])

// TODO: ganti dengan data dari API sesuai controller Auditor
const jadwalAudit = ref([
  // { id: 1, tanggal: '28-07-2026', unit: 'Prodi Teknik Informatika', status: 'Terjadwal' },
])

function statusClass(status) {
  const map = {
    Terjadwal: 'bg-blue-50 text-blue-600',
    Berlangsung: 'bg-yellow-50 text-yellow-600',
    Selesai: 'bg-green-50 text-green-600',
  }
  return map[status] || 'bg-gray-100 text-gray-500'
}

const menus = ref([
  {
    icon: '📅',
    label: 'Jadwal Audit',
    description: 'Lihat jadwal audit yang ditugaskan',
    href: '/auditor/jadwal-audit',
  },
  {
    icon: '📝',
    label: 'Input Temuan',
    description: 'Catat hasil dan temuan audit',
    href: '/auditor/temuan',
  },
  {
    icon: '📄',
    label: 'Laporan Audit',
    description: 'Kelola dan unggah laporan audit',
    href: '/auditor/laporan',
  },
  {
    icon: '📊',
    label: 'Riwayat Audit',
    description: 'Lihat riwayat audit yang telah dilakukan',
    href: '/auditor/riwayat',
  },
])
</script>