<template>
  <div>
    <p class="text-sm text-gray-500 mb-6">
      Daftar dosen yang sedang ditugaskan sebagai Auditor atau Auditee, digabung 1 baris per dosen
      dari semua jadwal audit. Klik baris dosen buat lihat daftar jadwalnya, lalu klik salah satu
      jadwal buat langsung buka jadwal terkait (nambah/hapus penugasan dilakukan di sana).
    </p>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-gray-800">Auditor</h3>
        <div class="relative">
          <input
            v-model="searchAuditor"
            @input="handleSearchAuditor"
            type="text"
            class="block p-2 ps-3 text-sm text-gray-900 border border-gray-300 rounded-lg w-64 bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Cari nama dosen..."
          />
        </div>
      </div>
      <DosenJadwalGroupTable
        :items="dataAuditor"
        :loading="loadingAuditor"
        @go-to-jadwal="goToJadwal"
      />
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-gray-800">Auditee</h3>
        <div class="relative">
          <input
            v-model="searchAuditee"
            @input="handleSearchAuditee"
            type="text"
            class="block p-2 ps-3 text-sm text-gray-900 border border-gray-300 rounded-lg w-64 bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Cari nama dosen..."
          />
        </div>
      </div>
      <DosenJadwalGroupTable
        :items="dataAuditee"
        :loading="loadingAuditee"
        @go-to-jadwal="goToJadwal"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from 'lodash'
import axiosClient from '@/axios'
import DosenJadwalGroupTable from '@/components/DosenJadwalGroupTable.vue'

const router = useRouter()

const dataAuditor = ref([])
const dataAuditee = ref([])
const loadingAuditor = ref(true)
const loadingAuditee = ref(true)
const searchAuditor = ref('')
const searchAuditee = ref('')

// Backend sekarang mengembalikan bentuk digabung per dosen (bukan lagi flat per penugasan) kalau
// tidak ada param jadwal - lihat AuditorController/AuditeeController::index() (12 Sep). Tiap item:
// { dosen_id, nama_dosen, jumlah_jadwal, jadwal_list: [{ penugasan_id, jadwal_spmi_id,
// nama_jadwal, semester, is_ketua? }] }.
const getAuditor = async (filter = null) => {
  loadingAuditor.value = true
  try {
    const response = await axiosClient.post('/auditor/data', { filter })
    dataAuditor.value = response.data?.data ?? response.data ?? []
  } catch (error) {
    console.error('Gagal memuat daftar Auditor:', error)
  } finally {
    loadingAuditor.value = false
  }
}

const getAuditee = async (filter = null) => {
  loadingAuditee.value = true
  try {
    const response = await axiosClient.post('/auditee/data', { filter })
    dataAuditee.value = response.data?.data ?? response.data ?? []
  } catch (error) {
    console.error('Gagal memuat daftar Auditee:', error)
  } finally {
    loadingAuditee.value = false
  }
}

const handleSearchAuditor = debounce(() => getAuditor(searchAuditor.value), 500)
const handleSearchAuditee = debounce(() => getAuditee(searchAuditee.value), 500)

const goToJadwal = (jadwalSpmiId) => {
  if (!jadwalSpmiId) {
    console.warn('Baris jadwal tidak punya jadwal_spmi_id, tidak bisa buka halaman jadwal.')
    return
  }
  router.push({ path: '/admin/jadwal-audit', query: { edit: jadwalSpmiId } })
}

onMounted(() => {
  getAuditor()
  getAuditee()
})
</script>
