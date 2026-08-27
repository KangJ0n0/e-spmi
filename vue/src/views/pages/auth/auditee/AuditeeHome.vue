<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 mb-1">Dashboard Auditee</h1>
      <p class="text-gray-500 mb-8">Ringkasan audit unit Anda</p>

      <!-- Ringkasan / Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="bg-white rounded-lg shadow-sm border border-gray-100 p-5"
        >
          <p class="text-sm text-gray-500 mb-1">{{ stat.label }}</p>
          <p class="text-3xl font-bold text-gray-800">
            {{ isLoading ? '...' : stat.value }}
          </p>
          <p v-if="stat.note" class="text-xs text-gray-400 mt-1">{{ stat.note }}</p>
        </div>
      </div>

      <!-- Jadwal Audit Unit Saya -->
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Jadwal Audit Unit Saya</h2>
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-10">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
              <th class="px-5 py-3 font-medium">Tanggal</th>
              <th class="px-5 py-3 font-medium">Auditor</th>
              <th class="px-5 py-3 font-medium">Progres</th>
              <th class="px-5 py-3 font-medium">Status</th>
              <th class="px-5 py-3 font-medium"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="5" class="px-5 py-6 text-center text-gray-400">Memuat...</td>
            </tr>
            <tr
              v-else
              v-for="jadwal in jadwalAudit"
              :key="jadwal.jadwal_spmi_id"
              class="border-t border-gray-100 hover:bg-gray-50"
            >
              <td class="px-5 py-3 text-gray-700">
                {{ formatTanggal(jadwal.tanggal_awal) }} - {{ formatTanggal(jadwal.tanggal_akhir) }}
              </td>
              <td class="px-5 py-3 text-gray-700">{{ jadwal._auditorNama || '-' }}</td>
              <td class="px-5 py-3 text-gray-500 text-xs w-40">
                <div class="flex items-center justify-between mb-1">
                  <span>{{ jadwal._dijawab }}/{{ jadwal._total }} dijawab</span>
                  <span class="font-semibold text-gray-600">{{ persenJawab(jadwal) }}%</span>
                </div>
                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden mb-1">
                  <div
                    class="h-full bg-blue-500 rounded-full"
                    :style="{ width: persenJawab(jadwal) + '%' }"
                  ></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-400">
                  <span>{{ jadwal._dinilai }}/{{ jadwal._total }} dinilai</span>
                  <span v-if="jadwal._dinilai > 0">{{ jadwal._ks }} KS &middot; {{ jadwal._kts }} KTS</span>
                </div>
              </td>
              <td class="px-5 py-3">
                <span
                  class="text-xs px-2 py-1 rounded-full font-medium"
                  :class="statusClass(jadwal._status)"
                >
                  {{ jadwal._status }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <router-link
                  :to="{ path: '/auditee/instrumen-auditee', query: { id: jadwal.jadwal_spmi_id } }"
                  class="text-blue-600 hover:underline text-sm"
                >
                  Buka
                </router-link>
              </td>
            </tr>
            <tr v-if="!isLoading && jadwalAudit.length === 0">
              <td colspan="5" class="px-5 py-6 text-center text-gray-400">
                Belum ada jadwal audit untuk unit Anda
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Menu Navigasi - cuma halaman yang beneran ada route-nya. "Unggah Dokumen"/"Hasil
           Temuan"/"Rencana Tindak Lanjut" DIHAPUS dari sini - itu belum ada halamannya sendiri,
           semuanya sudah jadi bagian dari form "Mulai Evaluasi Diri" di Jadwal Audit. -->
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link
          v-for="menu in menus"
          :key="menu.label"
          :to="menu.to"
          class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-blue-200 transition flex flex-col items-start"
        >
          <span class="text-2xl mb-2">{{ menu.icon }}</span>
          <span class="font-medium text-gray-800">{{ menu.label }}</span>
          <span class="text-xs text-gray-400 mt-1">{{ menu.description }}</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'AuditeeDashboard' })

import { ref, computed, onMounted } from 'vue'
import axiosClient from '@/axios'

const jadwalAudit = ref([])
const isLoading = ref(false)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject) promise-nya
// untuk error 400/404/422/500 - jadi cek bentuk response-nya, sama seperti pola di file lain.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const formatTanggal = (tgl) => {
  if (!tgl) return '-'
  return new Date(tgl).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

// Status per jadwal dihitung dari progres beneran, dipecah jadi 2 tahap (jawab -> dinilai) biar
// "sampai mana prosesnya" kelihatan jelas: Auditee dulu yang harus selesai jawab semua soal,
// BARU tahap berikutnya Auditor menilai KS/KTS-nya.
const hitungStatus = (jadwal) => {
  if (jadwal._total === 0) return 'Belum Ada Soal'
  if (jadwal._dinilai === jadwal._total) return 'Selesai Dinilai'
  if (jadwal._dijawab === jadwal._total) return 'Menunggu Penilaian Auditor'
  const akhir = new Date(jadwal.tanggal_akhir + 'T23:59:59')
  if (new Date() > akhir) return 'Kedaluwarsa'
  if (jadwal._dijawab > 0) return 'Berlangsung'
  return 'Belum Dimulai'
}

const statusClass = (status) => {
  const map = {
    'Selesai Dinilai': 'bg-green-50 text-green-600',
    'Menunggu Penilaian Auditor': 'bg-blue-50 text-blue-600',
    Kedaluwarsa: 'bg-red-50 text-red-600',
    Berlangsung: 'bg-yellow-50 text-yellow-700',
    'Belum Dimulai': 'bg-gray-100 text-gray-500',
    'Belum Ada Soal': 'bg-gray-100 text-gray-500',
  }
  return map[status] || 'bg-gray-100 text-gray-500'
}

// Persentase soal yang SUDAH DIJAWAB per jadwal - dipakai buat progress bar di tabel. Progres
// PENILAIAN (KS/KTS) ditampilkan terpisah sebagai teks di bawahnya, karena itu tahap Auditor,
// bukan sesuatu yang bisa dikontrol Auditee.
const persenJawab = (jadwal) => {
  if (!jadwal._total) return 0
  return Math.round((jadwal._dijawab / jadwal._total) * 100)
}

const stats = computed(() => {
  const totalJadwal = jadwalAudit.value.length
  const totalBelumDijawab = jadwalAudit.value.reduce(
    (sum, j) => sum + Math.max((j._total || 0) - (j._dijawab || 0), 0),
    0,
  )
  const totalMenungguPenilaian = jadwalAudit.value.reduce(
    (sum, j) => sum + Math.max((j._dijawab || 0) - (j._dinilai || 0), 0),
    0,
  )
  const totalDinilai = jadwalAudit.value.reduce((sum, j) => sum + (j._dinilai || 0), 0)
  const totalKS = jadwalAudit.value.reduce((sum, j) => sum + (j._ks || 0), 0)
  const totalKTS = jadwalAudit.value.reduce((sum, j) => sum + (j._kts || 0), 0)
  return [
    { label: 'Jadwal Audit', value: totalJadwal, note: 'audit untuk unit Anda' },
    { label: 'Soal Belum Dijawab', value: totalBelumDijawab, note: 'perlu diisi & dilampiri bukti' },
    {
      label: 'Menunggu Penilaian',
      value: totalMenungguPenilaian,
      note: 'sudah dijawab, menunggu Auditor menilai',
    },
    { label: 'Sudah Dinilai Auditor', value: totalDinilai, note: `${totalKS} KS, ${totalKTS} KTS` },
  ]
})

const menus = ref([
  {
    icon: '📅',
    label: 'Jadwal Audit',
    description: 'Lihat jadwal audit unit Anda',
    to: '/auditee/jadwal-auditee',
  },
])

const fetchDashboard = async () => {
  isLoading.value = true
  const res = await axiosClient.get('/auditee/jadwal-saya')
  if (gagal(res)) {
    isLoading.value = false
    return
  }

  const jadwalList = res.data || []

  // Buat tiap jadwal: (1) ambil daftar soal buat hitung progres jawaban, (2) ambil nama Auditor
  // yang ditugaskan di jadwal itu (reuse endpoint Admin/Auditor `/auditor/data` yang sudah bisa
  // difilter per jadwal_spmi_id - lihat AuditorController::index - biar nggak perlu bikin
  // endpoint baru). Dipanggil paralel karena jumlah jadwal per Auditee biasanya kecil.
  const hasilPerJadwal = await Promise.all(
    jadwalList.map(async (jadwal) => {
      const [resSoal, resAuditor] = await Promise.all([
        axiosClient.get(`/jadwal-audit/${jadwal.jadwal_spmi_id}/pertanyaan`),
        axiosClient.post('/auditor/data', { jadwal_spmi_id: jadwal.jadwal_spmi_id }),
      ])
      const soal = gagal(resSoal) ? [] : resSoal.data || []
      const total = soal.length
      const dijawab = soal.filter((s) => s.jawaban?.deskripsi_hasil).length
      const dinilai = soal.filter((s) => s.jawaban?.status_temuan).length
      const ks = soal.filter((s) => s.jawaban?.status_temuan === 'KS').length
      const kts = soal.filter((s) => s.jawaban?.status_temuan === 'KTS').length

      const auditorList = gagal(resAuditor) ? [] : resAuditor.data || []
      const auditorNama = auditorList.map((a) => a.nama_dosen).filter(Boolean).join(', ')

      return {
        ...jadwal,
        _total: total,
        _dijawab: dijawab,
        _dinilai: dinilai,
        _ks: ks,
        _kts: kts,
        _auditorNama: auditorNama,
      }
    }),
  )

  hasilPerJadwal.forEach((j) => {
    j._status = hitungStatus(j)
  })

  jadwalAudit.value = hasilPerJadwal
  isLoading.value = false
}

onMounted(() => {
  fetchDashboard()
})
</script>
