<!--
  Modal kelola Kategori Berkas Instrumen - fitur baru 13 Sep 2026. Kategori TERPISAH dari
  Kategori Instrumen punya Bank Pertanyaan (dikonfirmasi user), tapi pola CRUD-nya sama persis
  dengan KategoriInstrumenModal.vue. Dipakai di halaman BerkasInstrumen.vue lewat tombol
  "Kelola Kategori". Emit 'changed' tiap ada perubahan biar parent refresh dropdown filternya.
-->
<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800">Kelola Kategori Berkas Instrumen</h3>
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
          placeholder="Nama kategori, mis. Dokumen Kurikulum"
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
        <!-- QOL fix (13 Sep 2026, dilaporkan user) - dulu modal ini kosong-melompong tanpa
        indikator apapun selama request /kategori-berkas-instrumen belum selesai, jadi transisi
        buka modal -> daftar kategori muncul terasa "nyentak" (sempat kelihatan seperti "belum ada
        kategori" padahal cuma masih loading). Sekarang ditampilkan spinner dulu. -->
        <div v-if="isFetching" class="px-3 py-4 text-sm text-gray-500 text-center">
          <span class="inline-flex items-center justify-center gap-2">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Memuat kategori...
          </span>
        </div>
        <p v-else-if="kategoriList.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
          Belum ada kategori berkas instrumen. Tambahkan lewat form di atas.
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
import { confirmDialog } from '@/utils/confirmDialog'

const emit = defineEmits(['close', 'changed'])

const kategoriList = ref([])
const namaInput = ref('')
const editingId = ref(null)
const isSaving = ref(false)
const errorMsg = ref('')
// QOL fix (13 Sep 2026, dilaporkan user) - lihat komentar di template bagian "Daftar kategori".
// Mulai true (bukan false) karena fetchKategori() langsung jalan pas modal ini di-mount.
const isFetching = ref(true)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 - jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const fetchKategori = async () => {
  isFetching.value = true
  try {
    const res = await axiosClient.get('/kategori-berkas-instrumen')
    if (gagal(res)) return
    kategoriList.value = res.data
  } finally {
    isFetching.value = false
  }
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
      ? await axiosClient.put(`/kategori-berkas-instrumen/${editingId.value}`, payload)
      : await axiosClient.post('/kategori-berkas-instrumen', payload)

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
  const ok = await confirmDialog(
    `Hapus kategori "${item.nama}"? Berkas yang sudah ada di kategori ini akan jadi "Tanpa Kategori", tidak ikut terhapus.`,
    { title: 'Hapus Kategori Berkas Instrumen', confirmText: 'Hapus', variant: 'danger' },
  )
  if (!ok) return

  const res = await axiosClient.delete(`/kategori-berkas-instrumen/${item.id}`)
  if (gagal(res)) return

  await fetchKategori()
  emit('changed')
}

onMounted(fetchKategori)
</script>
