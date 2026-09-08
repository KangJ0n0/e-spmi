<template>
  <div v-if="!show_form" class="p-6 bg-white rounded-lg shadow min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Manajemen Bank Pertanyaan</h1>
      <ButtonComponent variant="primary" @click="buttonTambah">
        + Tambah Pertanyaan Manual
      </ButtonComponent>
    </div>

    <!-- Section Upload Excel -->
    <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <h2 class="text-base font-semibold text-gray-800 mb-1">Import Soal dari Excel</h2>
      <p class="text-sm text-gray-500 mb-4">
        Format file .xlsx, .xls, atau .csv, dengan judul kolom (pernyataan_isi_standar,
        butir_pertanyaan, dokumen_akan_dicek) di baris ke-2.
      </p>
      <div class="flex flex-wrap items-center gap-3">
        <!-- Dulu pakai native <input type="file"> polos, tombol bawaan browser "Choose File"-nya
        kotak/flat nggak nyambung sama gaya rounded card di sekitarnya. Sekarang input aslinya
        disembunyikan, diganti label custom bertuliskan "Masukkan File" + nama file yang kepilih
        ditampilkan terpisah. -->
        <label
          for="excel-file-input"
          class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-semibold bg-green-700/10 text-green-800 hover:bg-green-700/20 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
          </svg>
          Masukkan File
        </label>
        <input
          id="excel-file-input"
          ref="fileInputRef"
          type="file"
          @change="handleFileUpload"
          accept=".xlsx, .xls, .csv"
          class="hidden"
        />
        <span class="text-sm text-gray-500 truncate max-w-[220px]">
          {{ file ? file.name : 'Belum ada file dipilih' }}
        </span>
        <!-- Tombol Import Excel: user minta hijau gelap khusus di sini (bukan navy ButtonComponent
        biasa), biar kebedaan sama tombol "+ Tambah Pertanyaan Manual" di atas. -->
        <button
          @click="submitUpload"
          :disabled="isLoading"
          class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-md text-sm font-medium disabled:opacity-50 transition-colors"
        >
          {{ isLoading ? 'Mengunggah...' : 'Import Excel' }}
        </button>
      </div>
    </div>

    <!-- Tabel Daftar Pertanyaan -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
              Pertanyaan / Pernyataan Standar
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
              Butir Pertanyaan
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
              Dokumen Dicek
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="bankList.length === 0">
            <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">
              Belum ada data pertanyaan. Silakan import atau tambah manual.
            </td>
          </tr>
          <tr v-else v-for="item in bankList" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.pertanyaan }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.butir_pertanyaan }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.dokumen_cek }}
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <button
                @click="buttonEdit(item)"
                class="text-blue-600 hover:text-blue-800 font-medium mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteItem(item.id)"
                class="text-red-600 hover:text-red-800 font-medium"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Dulu "Tambah Pertanyaan Manual" pakai modal popup polos (textarea putih tanpa gaya desain
  sistem, tombol Simpan biru bukan navy) - beda sendiri dari pola "Tambah Anggota"/"Tambah Jadwal"
  yang dipakai halaman lain (form full-page nempatin tabelnya, bukan modal). Sekarang dipindah ke
  pola yang sama lewat BankPertanyaanForm.vue. -->
  <BankPertanyaanForm
    v-if="show_form"
    :tipe="tipe_form"
    :data="data_awal"
    @back="buttonKembali"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axiosClient from '@/axios' // Sesuaikan path konfigurasi axios Anda
import ButtonComponent from '@/components/ButtonComponent.vue'
import BankPertanyaanForm from './BankPertanyaanForm.vue'

const bankList = ref([])
const file = ref(null)
const fileInputRef = ref(null)
const isLoading = ref(false)

// State form (page-swap, bukan modal lagi - lihat komentar di template)
const show_form = ref(false)
const tipe_form = ref('create')
const data_awal = ref({})

// Load data dari backend
const fetchBankList = async () => {
  try {
    const res = await axiosClient.get('/bank-pertanyaan')
    bankList.value = res.data
  } catch (error) {
    console.error('Gagal mengambil data pertanyaan:', error)
  }
}

// Handle File Excel
const handleFileUpload = (event) => {
  file.value = event.target.files[0]
}

const submitUpload = async () => {
  if (!file.value) {
    alert('Pilih file Excel terlebih dahulu!')
    return
  }

  isLoading.value = true
  const formData = new FormData()
  formData.append('file', file.value)

  try {
    const res = await axiosClient.post('/bank-pertanyaan/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    alert(res.data.message || 'Import berhasil!')
    file.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    await fetchBankList()
  } catch (error) {
    console.error('Error import:', error)
    alert(error.response?.data?.message || 'Gagal import data Excel.')
  } finally {
    isLoading.value = false
  }
}

// Handle form tambah/edit (page-swap)
const buttonTambah = () => {
  tipe_form.value = 'create'
  data_awal.value = {}
  show_form.value = true
}

const buttonEdit = (item) => {
  tipe_form.value = 'edit'
  data_awal.value = item
  show_form.value = true
}

const buttonKembali = async () => {
  show_form.value = false
  data_awal.value = {}
  await fetchBankList()
}

// Hapus Item
const deleteItem = async (id) => {
  if (!confirm('Yakin ingin menghapus butir pertanyaan ini?')) return
  try {
    await axiosClient.delete(`/bank-pertanyaan/${id}`)
    await fetchBankList()
  } catch (error) {
    console.error('Gagal menghapus:', error)
    alert('Gagal menghapus pertanyaan.')
  }
}

onMounted(() => {
  fetchBankList()
})
</script>
