<template>
  <div class="p-6 bg-white rounded-lg shadow min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Manajemen Bank Pertanyaan</h1>
      <button
        @click="openModal('create')"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold shadow"
      >
        + Tambah Pertanyaan Manual
      </button>
    </div>

    <!-- Section Upload Excel -->
    <div class="mb-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
      <h2 class="text-md font-semibold mb-3 text-gray-700">Import Soal dari Excel</h2>
      <div class="flex flex-wrap items-center gap-4">
        <input
          type="file"
          @change="handleFileUpload"
          accept=".xlsx, .xls, .csv"
          class="block w-full md:w-auto text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
        />
        <button
          @click="submitUpload"
          :disabled="isLoading"
          class="bg-green-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-green-700 disabled:opacity-50"
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
                @click="openModal('edit', item)"
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

    <!-- Modal Form (Create & Edit) -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center p-4 z-50"
    >
      <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-xl">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
          {{ modalMode === 'create' ? 'Tambah Pertanyaan Baru' : 'Edit Pertanyaan' }}
        </h3>
        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Pernyataan Isi Standar / Pertanyaan</label
            >
            <textarea
              v-model="form.pertanyaan"
              rows="3"
              required
              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Butir Pertanyaan</label>
            <textarea
              v-model="form.butir_pertanyaan"
              rows="3"
              required
              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Dokumen Akan Dicek (Satu per baris)</label
            >
            <textarea
              v-model="form.dokumen_cek"
              rows="3"
              required
              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axiosClient from '@/axios' // Sesuaikan path konfigurasi axios Anda

const bankList = ref([])
const file = ref(null)
const isLoading = ref(false)

// State Modal
const showModal = ref(false)
const modalMode = ref('create') // 'create' atau 'edit'
const editId = ref(null)

const form = reactive({
  pertanyaan: '',
  butir_pertanyaan: '',
  dokumen_cek: '',
})

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
    document.querySelector('input[type="file"]').value = ''
    await fetchBankList()
  } catch (error) {
    console.error('Error import:', error)
    alert(error.response?.data?.message || 'Gagal import data Excel.')
  } finally {
    isLoading.value = false
  }
}

// Handle Modal
const openModal = (mode, item = null) => {
  modalMode.value = mode
  if (mode === 'edit' && item) {
    editId.value = item.id
    form.pertanyaan = item.pertanyaan
    form.butir_pertanyaan = item.butir_pertanyaan
    form.dokumen_cek = item.dokumen_cek
  } else {
    editId.value = null
    form.pertanyaan = ''
    form.butir_pertanyaan = ''
    form.dokumen_cek = ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// Submit Create/Update
const submitForm = async () => {
  try {
    if (modalMode.value === 'create') {
      await axiosClient.post('/bank-pertanyaan', form)
      alert('Pertanyaan berhasil ditambahkan!')
    } else {
      await axiosClient.put(`/bank-pertanyaan/${editId.value}`, form)
      alert('Pertanyaan berhasil diperbarui!')
    }
    closeModal()
    await fetchBankList()
  } catch (error) {
    console.error('Gagal menyimpan:', error)
    alert('Gagal menyimpan data pertanyaan.')
  }
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
