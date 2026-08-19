<template>
  <!-- TOMBOL KEMBALI & JUDUL -->
  <div class="mb-4 flex justify-between items-center">
    <ButtonComponent variant="primary" @click="buttonBack"> Kembali </ButtonComponent>
    <h2 class="text-lg font-bold text-heading">Detail Jadwal & Penunjukan Dosen</h2>
  </div>

  <!-- ========================================== -->
  <!-- 1. BAGIAN ATAS: DETAIL INFO JADWAL -->
  <!-- ========================================== -->
  <div class="bg-white p-4 rounded-base shadow-xs border border-default-medium mb-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div>
        <p class="text-xs text-body mb-1">Nama Jadwal:</p>
        <p class="font-semibold text-heading text-sm">{{ data.nama_jadwal }}</p>
      </div>
      <div>
        <p class="text-xs text-body mb-1">Area Audit:</p>
        <p class="font-semibold text-heading text-sm">{{ data.area_audit }}</p>
      </div>
      <div>
        <p class="text-xs text-body mb-1">Tanggal Pelaksanaan:</p>
        <p class="font-semibold text-heading text-sm">
          {{ data.tanggal_awal }} s/d {{ data.tanggal_akhir }}
        </p>
      </div>
      <div>
        <p class="text-xs text-body mb-1">Semester:</p>
        <p class="font-semibold text-heading text-sm">{{ data.semester }}</p>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 2. BAGIAN BAWAH: 2 TABEL (AUDITOR & AUDITEE) -->
  <!-- ========================================== -->
  <div class="grid md:grid-cols-2 gap-6">
    <!-- TABEL 1: AUDITOR -->
    <div class="bg-white p-4 rounded-base shadow-xs border border-default-medium">
      <h3 class="text-md font-bold mb-4 text-brand">Daftar Auditor</h3>

      <!-- Form Input Auditor -->
      <div class="flex gap-2 mb-4">
        <div class="flex-grow">
          <Multiselect
            v-model="selected_auditor"
            :options="dosen_list"
            :searchable="true"
            placeholder="Ketik nama dosen auditor..."
            valueProp="id"
            label="nama_dosen"
            track-by="nama_dosen"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base"
          />
        </div>
        <button
          @click="addAuditor"
          class="bg-brand text-white px-4 py-2 rounded-base hover:bg-brand-strong text-sm font-medium"
        >
          Tambah
        </button>
      </div>

      <!-- List Data Auditor -->
      <table class="w-full text-sm text-left border-collapse">
        <thead>
          <tr class="bg-neutral-secondary-medium border-b border-default-medium">
            <th class="p-2">Nama Dosen</th>
            <th class="p-2 text-center" style="width: 80px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="auditor_list.length === 0">
            <td colspan="2" class="p-4 text-center text-body italic">
              Belum ada auditor ditugaskan.
            </td>
          </tr>
          <tr v-for="item in auditor_list" :key="item.id" class="border-b border-default-medium">
            <td class="p-2">{{ item.nama_dosen }}</td>
            <td class="p-2 text-center">
              <button
                @click="deleteAuditor(item.id)"
                class="text-red-500 hover:text-red-700 font-medium"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TABEL 2: AUDITEE (Yang Diaudit) -->
    <div class="bg-white p-4 rounded-base shadow-xs border border-default-medium">
      <h3 class="text-md font-bold mb-4 text-orange-600">Daftar Auditee</h3>

      <!-- Form Input Auditee -->
      <div class="flex gap-2 mb-4">
        <div class="flex-grow">
          <Multiselect
            v-model="selected_auditee"
            :options="dosen_list"
            :searchable="true"
            placeholder="Ketik nama dosen auditee..."
            valueProp="id"
            label="nama_dosen"
            track-by="nama_dosen"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base"
          />
        </div>
        <button
          @click="addAuditee"
          class="bg-orange-600 text-white px-4 py-2 rounded-base hover:bg-orange-700 text-sm font-medium"
        >
          Tambah
        </button>
      </div>

      <!-- List Data Auditee -->
      <table class="w-full text-sm text-left border-collapse">
        <thead>
          <tr class="bg-neutral-secondary-medium border-b border-default-medium">
            <th class="p-2">Nama Dosen</th>
            <th class="p-2 text-center" style="width: 80px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="auditee_list.length === 0">
            <td colspan="2" class="p-4 text-center text-body italic">
              Belum ada auditee ditugaskan.
            </td>
          </tr>
          <tr v-for="item in auditee_list" :key="item.id" class="border-b border-default-medium">
            <td class="p-2">{{ item.nama_dosen }}</td>
            <td class="p-2 text-center">
              <button
                @click="deleteAuditee(item.id)"
                class="text-red-500 hover:text-red-700 font-medium"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ButtonComponent from '../../../../components/ButtonComponent.vue'
import Multiselect from '@vueform/multiselect'
import axiosClient from '@/axios'
import '../../../../css/select.css'

const emit = defineEmits(['back'])

// Menerima data jadwal yang diklik dari tabel utama
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
})

const dosen_list = ref([])
const auditor_list = ref([])
const auditee_list = ref([])

const selected_auditor = ref(null)
const selected_auditee = ref(null)

// --- AMBIL DATA MASTER DOSEN UNTUK DROPDOWN ---
const fetchDosen = async () => {
  try {
    // Ganti URL ini dengan endpoint master dosen yang Anda miliki di api.php
    const response = await axiosClient.get('/dosen')
    dosen_list.value = response.data.data ? response.data.data : response.data
  } catch (error) {
    console.error('Gagal mengambil data dosen:', error)
  }
}

// --- FUNGSI UNTUK AUDITOR ---
const fetchAuditor = async () => {
  try {
    const response = await axiosClient.post('/auditor', { jadwal_id: props.data.id })
    auditor_list.value = response.data.data ? response.data.data : response.data
  } catch (error) {
    console.error('Gagal memuat auditor:', error)
  }
}

const addAuditor = async () => {
  if (!selected_auditor.value) return alert('Pilih dosen terlebih dahulu!')
  try {
    await axiosClient.post('/auditor/store', {
      jadwal_spmi_id: props.data.id,
      dosen_id: selected_auditor.value,
    })
    selected_auditor.value = null
    fetchAuditor() // Refresh tabel
  } catch (error) {
    alert(error.response?.data?.error || 'Gagal menambah auditor')
  }
}

const deleteAuditor = async (id) => {
  if (confirm('Hapus dosen ini dari daftar Auditor?')) {
    try {
      await axiosClient.delete(`/auditor/${id}`)
      fetchAuditor()
    } catch (error) {
      alert('Gagal menghapus auditor')
    }
  }
}

// --- FUNGSI UNTUK AUDITEE ---
const fetchAuditee = async () => {
  try {
    const response = await axiosClient.post('/auditee', { jadwal_id: props.data.id })
    auditee_list.value = response.data.data ? response.data.data : response.data
  } catch (error) {
    console.error('Gagal memuat auditee:', error)
  }
}

const addAuditee = async () => {
  if (!selected_auditee.value) return alert('Pilih dosen terlebih dahulu!')
  try {
    await axiosClient.post('/auditee/store', {
      jadwal_spmi_id: props.data.id,
      dosen_id: selected_auditee.value,
    })
    selected_auditee.value = null
    fetchAuditee() // Refresh tabel
  } catch (error) {
    alert(error.response?.data?.error || 'Gagal menambah auditee')
  }
}

const deleteAuditee = async (id) => {
  if (confirm('Hapus dosen ini dari daftar Auditee?')) {
    try {
      await axiosClient.delete(`/auditee/${id}`)
      fetchAuditee()
    } catch (error) {
      alert('Gagal menghapus auditee')
    }
  }
}

const buttonBack = () => {
  emit('back')
}

// Saat halaman detail ini dibuka, langsung load semua data
onMounted(() => {
  fetchDosen()
  fetchAuditor()
  fetchAuditee()
})
</script>
