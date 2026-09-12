<template>
  <div v-if="!show_form" class="p-6 bg-white rounded-lg shadow min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Manajemen Instrumen</h1>
      <ButtonComponent variant="primary" @click="buttonTambah">
        + Tambah Pertanyaan Manual
      </ButtonComponent>
    </div>

    <!-- Kategori Instrumen (fitur baru 9 Sep 2026) - "ruang" instrumen sendiri (mis. LAMEMBA)
    yang bisa dibikin Admin, dipakai buat nyaring soal & nentuin kategori soal baru pas tambah
    manual/import Excel di bawah. "Semua Kategori" = perilaku lama (semua soal tampil, apa pun
    kategorinya). -->
    <div class="mb-6 flex flex-wrap items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
      <label class="text-sm font-medium text-gray-700 shrink-0">Kategori Instrumen:</label>
      <!-- Dulu select ini nggak punya appearance-none/padding-kanan, jadi teks "Semua Kategori"
      nabrak ikon panah bawaan browser (dilaporkan user 10 Sep). Sekarang dirapikan pakai pola
      yang sama dengan dropdown "Tampilkan X data" di TableComponent.vue: appearance-none + ikon
      panah custom sendiri yang dikasih jarak (pr-8) dari teks. -->
      <div class="relative">
        <select
          v-model="kategoriAktif"
          class="appearance-none border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="all">Semua Kategori</option>
          <option value="none">Tanpa Kategori</option>
          <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
        </select>
        <svg
          class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
      <button
        @click="showKategoriModal = true"
        class="text-sm font-medium text-blue-600 hover:text-blue-800"
      >
        + Kelola Kategori
      </button>
    </div>

    <!-- Section Upload Excel -->
    <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <h2 class="text-base font-semibold text-gray-800 mb-1">Import Soal dari Excel</h2>
      <p class="text-sm text-gray-500 mb-4">
        Format file .xlsx, .xls, atau .csv, dengan judul kolom (pernyataan_isi_standar,
        butir_pertanyaan, dokumen_akan_dicek) di baris ke-2.
      </p>
      <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori soal hasil import ini</label>
        <select
          v-model="kategoriUpload"
          class="border border-gray-300 rounded-md px-3 py-2 text-sm w-64 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Tanpa Kategori</option>
          <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
        </select>
      </div>
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
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
              Kategori
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="bankListTampil.length === 0">
            <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">
              Belum ada data pertanyaan. Silakan import atau tambah manual.
            </td>
          </tr>
          <tr v-else v-for="item in bankListTampil" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.pertanyaan }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.butir_pertanyaan }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.dokumen_cek }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">
              <span
                v-if="item.kategori_instrumen"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"
              >
                {{ item.kategori_instrumen.nama }}
              </span>
              <span v-else class="text-xs text-gray-400 italic">Tanpa Kategori</span>
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
    :kategori-list="kategoriList"
    @back="buttonKembali"
  />

  <!-- Modal kelola Kategori Instrumen (fitur baru 9 Sep 2026) -->
  <KategoriInstrumenModal
    v-if="showKategoriModal"
    @close="showKategoriModal = false"
    @changed="fetchKategoriList"
  />
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axiosClient from '@/axios' // Sesuaikan path konfigurasi axios Anda
import ButtonComponent from '@/components/ButtonComponent.vue'
import BankPertanyaanForm from './BankPertanyaanForm.vue'
import KategoriInstrumenModal from '@/components/KategoriInstrumenModal.vue'
import { notifyError } from '@/utils/notify'

const bankList = ref([])
const file = ref(null)
const fileInputRef = ref(null)
const isLoading = ref(false)

// Kategori Instrumen (fitur baru 9 Sep 2026) - "all" = Semua Kategori (perilaku lama, semua
// soal tampil), "none" = cuma yang belum dikategorikan, selain itu = id kategori tertentu.
const kategoriList = ref([])
const kategoriAktif = ref('all')
const kategoriUpload = ref('') // dipakai saat submitUpload, '' = Tanpa Kategori
const showKategoriModal = ref(false)

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

const fetchKategoriList = async () => {
  try {
    const res = await axiosClient.get('/kategori-instrumen')
    kategoriList.value = res.data
  } catch (error) {
    console.error('Gagal mengambil data kategori instrumen:', error)
  }
}

// Filter tampilan tabel berdasarkan kategori aktif - dilakukan di client (data soal biasanya
// tidak banyak, sama seperti pola pencarian di PilihPertanyaanAuditor.vue), bukan re-fetch ke
// server tiap ganti dropdown.
const bankListTampil = computed(() => {
  if (kategoriAktif.value === 'all') return bankList.value
  if (kategoriAktif.value === 'none') return bankList.value.filter((i) => !i.kategori_instrumen)
  return bankList.value.filter((i) => i.kategori_instrumen?.id === kategoriAktif.value)
})

// Kalau kategori aktif dipilih spesifik (bukan "Semua"/"Tanpa Kategori"), soal baru (manual/
// import) otomatis diarahkan ke kategori yang sama - biar alur "buka ruang LAMEMBA -> upload/
// tambah soal di situ" nggak perlu pilih ulang tiap kali.
watch(kategoriAktif, (val) => {
  kategoriUpload.value = val !== 'all' && val !== 'none' ? val : ''
})

// Handle File Excel
const handleFileUpload = (event) => {
  file.value = event.target.files[0]
}

const submitUpload = async () => {
  if (!file.value) {
    notifyError('Pilih file Excel terlebih dahulu!')
    return
  }

  isLoading.value = true
  const formData = new FormData()
  formData.append('file', file.value)
  // Kategori dipilih di dropdown atas tombol upload (fitur baru 9 Sep 2026) - kosong berarti
  // "Tanpa Kategori", sama seperti sebelum fitur ini ada.
  if (kategoriUpload.value) formData.append('kategori_instrumen_id', kategoriUpload.value)

  try {
    // Toast sukses ("Berhasil import N butir...") sudah otomatis ditampilkan interceptor axios.js
    // dari `message` yang dibalikin backend - dulu ada alert() manual duplikat di sini (10 Sep,
    // dirapikan waktu rapiin notifikasi jadi 1 pola konsisten).
    await axiosClient.post('/bank-pertanyaan/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    file.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    await fetchBankList()
  } catch (error) {
    console.error('Error import:', error)
    notifyError(error.response?.data?.message || 'Gagal import data Excel.')
  } finally {
    isLoading.value = false
  }
}

// Handle form tambah/edit (page-swap)
const buttonTambah = () => {
  tipe_form.value = 'create'
  // Kalau kategori aktif sedang dipilih spesifik (bukan "Semua"/"Tanpa Kategori"), soal
  // manual baru default masuk kategori itu juga - bisa diganti manual di form kalau perlu.
  const kategoriDefault = kategoriAktif.value !== 'all' && kategoriAktif.value !== 'none' ? kategoriAktif.value : ''
  data_awal.value = { kategori_instrumen_id: kategoriDefault }
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
    notifyError('Gagal menghapus pertanyaan.')
  }
}

onMounted(() => {
  fetchBankList()
  fetchKategoriList()
})
</script>
