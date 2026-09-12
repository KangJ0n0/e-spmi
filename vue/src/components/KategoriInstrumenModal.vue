<!--
  Modal kelola Kategori Instrumen (mis. "LAMEMBA") - fitur baru 9 Sep 2026. Dipakai di halaman
  Instrumen (BankPertanyaan.vue) lewat tombol "Kelola Kategori". CRUD sederhana: tambah, edit
  nama, hapus. Emit 'changed' tiap ada perubahan biar parent refresh dropdown filternya.
-->
<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800">Kelola Kategori Instrumen</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form tambah/edit -->
      <form @submit.prevent="submitForm" class="flex items-center gap-2 mb-4">
        <input
          v-model="namaInput"
          type="text"
          placeholder="Nama kategori, mis. LAMEMBA"
          required
          class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
        />
        <button
          type="submit"
          :disabled="isSaving"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50"
        >
          {{ editingId ? 'Simpan' : 'Tambah' }}
        </button>
        <button
          v-if="editingId"
          type="button"
          @click="batalEdit"
          class="text-sm text-gray-500 hover:text-gray-700 px-2"
        >
          Batal
        </button>
      </form>
      <p v-if="errorMsg" class="text-xs text-red-600 -mt-2 mb-3">{{ errorMsg }}</p>

      <!-- Daftar kategori -->
      <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-md divide-y divide-gray-100">
        <p v-if="kategoriList.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
          Belum ada kategori instrumen. Tambahkan lewat form di atas.
        </p>
        <div
          v-for="item in kategoriList"
          :key="item.id"
          class="flex items-center justify-between px-3 py-2 hover:bg-gray-50"
        >
          <span class="text-sm text-gray-800">{{ item.nama }}</span>
          <div class="flex items-center gap-3 shrink-0">
            <button @click="mulaiEdit(item)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
              Edit
            </button>
            <button @click="hapusKategori(item)" class="text-red-600 hover:text-red-800 text-xs font-medium">
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axiosClient from '@/axios'

const emit = defineEmits(['close', 'changed'])

const kategoriList = ref([])
const namaInput = ref('')
const editingId = ref(null)
const isSaving = ref(false)
const errorMsg = ref('')

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 - jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const fetchKategori = async () => {
  const res = await axiosClient.get('/kategori-instrumen')
  if (gagal(res)) return
  kategoriList.value = res.data
}

const mulaiEdit = (item) => {
  editingId.value = item.id
  namaInput.value = item.nama
  errorMsg.value = ''
}

const batalEdit = () => {
  editingId.value = null
  namaInput.value = ''
  errorMsg.value = ''
}

const submitForm = async () => {
  isSaving.value = true
  errorMsg.value = ''
  try {
    const payload = { nama: namaInput.value.trim() }
    const res = editingId.value
      ? await axiosClient.put(`/kategori-instrumen/${editingId.value}`, payload)
      : await axiosClient.post('/kategori-instrumen', payload)

    if (gagal(res)) {
      errorMsg.value = res.response?.data?.errors?.nama?.[0] || res.response?.data?.message || 'Gagal menyimpan kategori.'
      return
    }

    batalEdit()
    await fetchKategori()
    emit('changed')
  } finally {
    isSaving.value = false
  }
}

const hapusKategori = async (item) => {
  if (!confirm(`Hapus kategori "${item.nama}"? Soal yang sudah ada di kategori ini akan jadi "Tanpa Kategori", tidak ikut terhapus.`)) return

  const res = await axiosClient.delete(`/kategori-instrumen/${item.id}`)
  if (gagal(res)) return

  await fetchKategori()
  emit('changed')
}

onMounted(fetchKategori)
</script>
