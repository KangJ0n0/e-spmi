<template>
  <div>
    <p class="text-sm text-gray-500 mb-6">
      Daftar dosen yang sedang ditugaskan sebagai Auditor atau Auditee, dari semua jadwal audit.
      Klik <span class="font-medium text-gray-700">Edit</span> buat langsung buka jadwal terkait
      (nambah/hapus penugasan dilakukan di sana).
    </p>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mb-6">
      <h3 class="text-base font-semibold text-gray-800 mb-4">Auditor</h3>
      <TableComponent
        :headers="headers"
        :dataTable="dataAuditor"
        :loading="loadingAuditor"
        :no_paginate="true"
        :show_search="true"
        @search="handleSearchAuditor"
        @edit="goToJadwal"
      ></TableComponent>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <h3 class="text-base font-semibold text-gray-800 mb-4">Auditee</h3>
      <TableComponent
        :headers="headers"
        :dataTable="dataAuditee"
        :loading="loadingAuditee"
        :no_paginate="true"
        :show_search="true"
        @search="handleSearchAuditee"
        @edit="goToJadwal"
      ></TableComponent>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from 'lodash'
import axiosClient from '@/axios'
import TableComponent from '@/components/TableComponent.vue'

const router = useRouter()

const headers = [
  { key: 'nama_dosen', label: 'Nama Dosen', view: 'title' },
  { key: 'nama_jadwal', label: 'Jadwal Audit' },
  { button: ['Edit'] },
]

const dataAuditor = ref([])
const dataAuditee = ref([])
// Mulai true supaya langsung nampilin "Memuat data..." begitu halaman dibuka (bukan
// "Data Not Found" dulu) - lihat pola yang sama di JadwalAudit.vue/strukturanggota.js.
const loadingAuditor = ref(true)
const loadingAuditee = ref(true)

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

const handleSearchAuditor = debounce((query) => getAuditor(query), 500)
const handleSearchAuditee = debounce((query) => getAuditee(query), 500)

// Tombol "Edit" di baris manapun (Auditor/Auditee) nggak ngedit baris penugasan ini langsung -
// dia buka halaman Jadwal Audit dalam mode Edit buat jadwal yang bersangkutan (id-nya dari
// jadwal_spmi_id), karena di situ tempat penugasan Auditor/Auditee beneran dikelola.
const goToJadwal = (row) => {
  if (!row?.jadwal_spmi_id) {
    console.warn('Baris ini nggak punya jadwal_spmi_id, nggak bisa dibuka:', row)
    return
  }
  router.push({ path: '/admin/jadwal-audit', query: { edit: row.jadwal_spmi_id } })
}

onMounted(() => {
  getAuditor()
  getAuditee()
})
</script>

<style scoped></style>
