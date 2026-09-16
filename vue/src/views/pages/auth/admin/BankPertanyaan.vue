<template>
  <div v-if="!show_form" class="p-6 bg-white rounded-lg shadow min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Manajemen Instrumen</h1>
      <div class="flex items-center gap-3">
        <!-- Fitur baru (15 Sep 2026) - card Import Excel dulu SELALU kebuka di layar (bareng
        dropdown filter Kategori Instrumen di atas + dropdown Kategori khusus Import di dalam card
        - 2 dropdown kategori sekaligus kelihatan bikin bingung, dilaporkan user). Sekarang card-nya
        disembunyikan default, cuma muncul kalau tombol ini diklik. -->
        <button
          @click="showImportCard = !showImportCard"
          class="px-4 py-2 rounded-md text-sm font-medium border border-green-700 text-green-800 hover:bg-green-50 transition-colors"
        >
          {{ showImportCard ? 'Tutup Import Excel' : '+ Import dari Excel' }}
        </button>
        <ButtonComponent variant="primary" @click="buttonTambah">
          + Tambah Pertanyaan Manual
        </ButtonComponent>
      </div>
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
      <!-- Fitur baru (15 Sep 2026) - "Hapus Semua di Kategori Ini", user minta cara cepat kosongin
      1 kategori (mis. abis import salah/mau ulang) tanpa hapus manual satu-satu. SENGAJA cuma
      muncul kalau kategoriAktif spesifik (bukan "Semua Kategori") - biar Admin nggak nggak sengaja
      hapus SELURUH bank soal cuma karena lupa ganti dari "Semua Kategori" dulu. -->
      <button
        v-if="kategoriAktif !== 'all' && pagination.total > 0"
        @click="hapusSemuaKategoriAktif"
        :disabled="isHapusMassal"
        class="text-sm font-medium text-red-600 hover:text-red-800 disabled:opacity-50"
      >
        {{ isHapusMassal ? 'Menghapus...' : `Hapus Semua (${pagination.total})` }}
      </button>
      <!-- QOL fix (12 Sep 2026) - dulu tidak ada pencarian teks sama sekali di halaman ini
      (cuma filter kategori), padahal ini halaman bank soal yang bisa berisi ribuan butir. -->
      <div class="relative ml-auto">
        <input
          type="text"
          @input="filterSearch($event.target.value)"
          placeholder="Cari pertanyaan / butir / dokumen..."
          class="border border-gray-300 rounded-md pl-3 pr-3 py-2 text-sm w-64 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
    </div>

    <!-- Section Upload Excel - sekarang v-if, cuma muncul kalau tombol "+ Import dari Excel" di
    header diklik (lihat komentar di atas). -->
    <div v-if="showImportCard" class="mb-8 bg-white rounded-lg shadow-sm border border-gray-100 p-5">
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

      <!-- QOL fix (12 Sep 2026) - dulu Admin cuma dapat 1 toast generik ("Berhasil import N
      butir...") tanpa tahu kalau ada baris yang diam-diam dilewati/gagal. Sekarang detail per
      baris ditampilkan di sini kalau ada (lihat BankPertanyaanImport::$skipped). -->
      <div
        v-if="importResult && (importResult.peringatan?.length || importResult.info?.length)"
        class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4"
      >
        <div class="flex items-start justify-between gap-3">
          <p class="text-sm font-semibold text-amber-800">
            {{ importResult.berhasil ?? 0 }} dari {{ importResult.total_baris ?? '?' }} baris berhasil
            diimport. {{ (importResult.peringatan?.length || 0) + (importResult.info?.length || 0) }}
            baris dilewati - detail:
          </p>
          <button @click="dismissImportResult" class="text-amber-600 hover:text-amber-800 text-sm shrink-0">
            Tutup
          </button>
        </div>
        <ul class="mt-2 space-y-1 text-sm text-amber-700 max-h-48 overflow-y-auto">
          <li v-for="(s, idx) in [...(importResult.peringatan || []), ...(importResult.info || [])]" :key="idx">
            <span class="font-medium">Baris {{ s.baris }}</span
            >{{ s.tipe === 'peringatan' ? ' ⚠️' : '' }} — {{ s.alasan }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Tabel Daftar Pertanyaan -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <!-- Fitur baru (15 Sep 2026) - kolom No urut (mengikuti urutan tampil di tabel, sama
            seperti nomor urut di file Excel sumbernya), dulu nggak ada sama sekali di halaman ini
            (beda dari halaman Auditor/Auditee yang sudah ada semua). Urutannya ikut halaman
            pagination yang aktif, BUKAN reset ke 1 tiap halaman. -->
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-14">
              No
            </th>
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
          <tr v-if="isFetching && bankList.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
              <span class="inline-flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memuat data...
              </span>
            </td>
          </tr>
          <tr v-else-if="bankList.length === 0">
            <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
              Belum ada data pertanyaan. Silakan import atau tambah manual.
            </td>
          </tr>
          <tr v-else v-for="(item, index) in bankList" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-500 text-center">
              {{ (pagination.page - 1) * pagination.per_page + index + 1 }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-800" v-html="formatTeksBernomor(item.pertanyaan)"></td>
            <td class="px-4 py-3 text-sm text-gray-800" v-html="formatTeksBernomor(item.butir_pertanyaan)"></td>
            <td class="px-4 py-3 text-sm text-gray-800" v-html="formatTeksBernomor(item.dokumen_cek)"></td>
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

    <!-- QOL fix (12 Sep 2026) - pagination server-side, gantinya narik semua data sekaligus. -->
    <nav
      v-if="pagination.total > 0"
      class="flex items-center flex-wrap justify-between pt-4 gap-3"
      aria-label="Table navigation"
    >
      <span class="text-sm text-gray-500">
        Halaman <span class="font-semibold text-gray-800">{{ pagination.page }}</span> dari
        <span class="font-semibold text-gray-800">{{ pagination.last_page }}</span>
        ({{ pagination.total }} total soal)
      </span>
      <div class="inline-flex -space-x-px text-sm h-8">
        <button
          @click="gotoPage(pagination.page - 1)"
          :disabled="pagination.page <= 1"
          class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
        >
          Previous
        </button>
        <button
          @click="gotoPage(pagination.page + 1)"
          :disabled="pagination.page >= pagination.last_page"
          class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
        >
          Next
        </button>
      </div>
    </nav>
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
import { ref, reactive, watch, onMounted } from 'vue'
import { debounce } from 'lodash'
import axiosClient from '@/axios' // Sesuaikan path konfigurasi axios Anda
import ButtonComponent from '@/components/ButtonComponent.vue'
import BankPertanyaanForm from './BankPertanyaanForm.vue'
import KategoriInstrumenModal from '@/components/KategoriInstrumenModal.vue'
import { notifyError } from '@/utils/notify'
import { confirmDialog } from '@/utils/confirmDialog'
// Fitur baru (15 Sep 2026) - render teks list bernomor manual ("1) ... 2) ...") jadi <ol> beneran
// di tabel, bukan cuma teks numpuk. Lihat CHANGES.md.
import { formatTeksBernomor } from '@/utils/formatTeksBernomor'

const bankList = ref([])
const file = ref(null)
const fileInputRef = ref(null)
const isLoading = ref(false)
// QOL fix (12 Sep 2026) - indikator loading tabel (dulu tidak ada sama sekali di halaman ini).
const isFetching = ref(true)
// Fitur baru (15 Sep 2026) - state loading tombol "Hapus Semua di Kategori Ini".
const isHapusMassal = ref(false)
// Fitur baru (15 Sep 2026) - card Import Excel default ketutup, biar nggak numpuk 2 dropdown
// kategori (filter atas + kategori upload di dalam card) kelihatan bareng terus.
const showImportCard = ref(false)

// Kategori Instrumen (fitur baru 9 Sep 2026) - "all" = Semua Kategori (perilaku lama, semua
// soal tampil), "none" = cuma yang belum dikategorikan, selain itu = id kategori tertentu.
const kategoriList = ref([])
const kategoriAktif = ref('all')
const kategoriUpload = ref('') // dipakai saat submitUpload, '' = Tanpa Kategori
const showKategoriModal = ref(false)

// QOL fix (12 Sep 2026): pencarian + pagination SERVER-SIDE, gantinya narik SEMUA soal sekaligus
// lalu difilter di client (bankListTampil lama) - lihat catatan lengkap di
// BankPertanyaanController::index(). Halaman ini ("senjata rahasia" ETL skripsi, bisa berisi
// ribuan butir soal) yang paling kepengaruh kalau data beneran banyak - beda dengan
// PilihPertanyaanAuditor.vue yang SENGAJA TIDAK dipaginasi (butuh SEMUA soal buat fitur "Kirim
// Semua per Kategori").
const searchQuery = ref('')
const pagination = reactive({
  page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
})

// State form (page-swap, bukan modal lagi - lihat komentar di template)
const show_form = ref(false)
const tipe_form = ref('create')
const data_awal = ref({})

// Load data dari backend
const fetchBankList = async () => {
  isFetching.value = true
  try {
    const params = { paginate: pagination.per_page, page: pagination.page }
    if (searchQuery.value) params.filter = searchQuery.value
    if (kategoriAktif.value !== 'all') params.kategori_instrumen_id = kategoriAktif.value

    const res = await axiosClient.get('/bank-pertanyaan', { params })
    bankList.value = res.data.data ?? []
    pagination.total = res.data.total ?? bankList.value.length
    pagination.last_page = res.data.last_page ?? 1

    // Kalau halaman sekarang jadi kosong (mis. abis hapus 1-1 nya item terakhir di halaman ini)
    // dan bukan halaman pertama, mundur otomatis 1 halaman daripada nampilin tabel kosong.
    if (bankList.value.length === 0 && pagination.page > 1) {
      pagination.page -= 1
      await fetchBankList()
      return
    }
  } catch (error) {
    console.error('Gagal mengambil data pertanyaan:', error)
  } finally {
    isFetching.value = false
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

// Pencarian di-debounce 500ms (pola sama dengan StrukturAnggota.vue) - baru manggil server
// setelah user berhenti ngetik sesaat, bukan tiap huruf.
const filterSearch = debounce(async (query) => {
  searchQuery.value = query
  pagination.page = 1
  await fetchBankList()
}, 500)

// Kalau kategori aktif dipilih spesifik (bukan "Semua"/"Tanpa Kategori"), soal baru (manual/
// import) otomatis diarahkan ke kategori yang sama - biar alur "buka ruang LAMEMBA -> upload/
// tambah soal di situ" nggak perlu pilih ulang tiap kali. Ganti kategori filter sekarang juga
// nge-refetch ke server (dulu murni filter client dari data yang sudah ditarik semua).
watch(kategoriAktif, async (val) => {
  kategoriUpload.value = val !== 'all' && val !== 'none' ? val : ''
  pagination.page = 1
  await fetchBankList()
})

const gotoPage = async (page) => {
  if (page < 1 || page > pagination.last_page || page === pagination.page) return
  pagination.page = page
  await fetchBankList()
}

// Handle File Excel
const handleFileUpload = (event) => {
  file.value = event.target.files[0]
}

// QOL fix (12 Sep 2026): hasil import Excel sekarang disimpan lengkap (bukan cuma toast sukses/
// gagal total) - dipakai buat nampilin panel "N baris dilewati" di bawah kalau ada, lihat
// BankPertanyaanController::importExcel()/BankPertanyaanImport::$skipped.
const importResult = ref(null)
const dismissImportResult = () => {
  importResult.value = null
}

const submitUpload = async () => {
  if (!file.value) {
    notifyError('Pilih file Excel terlebih dahulu!')
    return
  }

  isLoading.value = true
  importResult.value = null
  const formData = new FormData()
  formData.append('file', file.value)
  // Kategori dipilih di dropdown atas tombol upload (fitur baru 9 Sep 2026) - kosong berarti
  // "Tanpa Kategori", sama seperti sebelum fitur ini ada.
  if (kategoriUpload.value) formData.append('kategori_instrumen_id', kategoriUpload.value)

  try {
    // Toast sukses ("Berhasil import N butir...") sudah otomatis ditampilkan interceptor axios.js
    // dari `message` yang dibalikin backend - dulu ada alert() manual duplikat di sini (10 Sep,
    // dirapikan waktu rapiin notifikasi jadi 1 pola konsisten).
    const res = await axiosClient.post('/bank-pertanyaan/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    importResult.value = res.data
    file.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    pagination.page = 1
    await fetchBankList()
  } catch (error) {
    console.error('Error import:', error)
    notifyError(error.response?.data?.message || 'Gagal import data Excel.')
    // Backend tetap balikin detail peringatan walau statusnya "0 data masuk" (400) - lihat
    // BankPertanyaanController::importExcel() - jadi tetap ditampilkan kalau ada.
    if (error.response?.data?.peringatan) {
      importResult.value = error.response.data
    }
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
  const ok = await confirmDialog('Yakin ingin menghapus butir pertanyaan ini?', {
    title: 'Hapus Pertanyaan',
    confirmText: 'Hapus',
    variant: 'danger',
  })
  if (!ok) return
  try {
    await axiosClient.delete(`/bank-pertanyaan/${id}`)
    await fetchBankList()
  } catch (error) {
    console.error('Gagal menghapus:', error)
    notifyError('Gagal menghapus pertanyaan.')
  }
}

// Fitur baru (15 Sep 2026) - Hapus Semua soal di kategori yang lagi aktif difilter. SENGAJA cuma
// bisa dipanggil kalau kategoriAktif spesifik (tombolnya juga cuma muncul dalam kondisi itu, lihat
// template) - proteksi ganda biar nggak ada jalan buat nge-wipe SELURUH bank soal cuma dari sini.
const hapusSemuaKategoriAktif = async () => {
  if (kategoriAktif.value === 'all') return

  const namaKategori =
    kategoriAktif.value === 'none'
      ? 'Tanpa Kategori'
      : kategoriList.value.find((k) => k.id === kategoriAktif.value)?.nama || 'kategori ini'

  const ok = await confirmDialog(
    `Yakin ingin menghapus SEMUA ${pagination.total} soal di kategori "${namaKategori}"? Aksi ini tidak bisa dibatalkan.`,
    { title: 'Hapus Semua Soal', confirmText: 'Hapus Semua', variant: 'danger' },
  )
  if (!ok) return

  isHapusMassal.value = true
  try {
    await axiosClient.delete('/bank-pertanyaan/hapus-massal', {
      params: { kategori_instrumen_id: kategoriAktif.value },
    })
    pagination.page = 1
    await fetchBankList()
  } catch (error) {
    console.error('Gagal hapus massal:', error)
    notifyError(error.response?.data?.message || 'Gagal menghapus soal.')
  } finally {
    isHapusMassal.value = false
  }
}

onMounted(() => {
  fetchBankList()
  fetchKategoriList()
})
</script>
