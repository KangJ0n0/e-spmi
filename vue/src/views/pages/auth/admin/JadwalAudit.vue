<template>
  <div v-if="!show_form">
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-bottom: 1rem">
      <ButtonComponent variant="secondary" @click="show_import_panel = !show_import_panel">
        {{ show_import_panel ? 'Tutup Import' : 'Import Excel' }}
      </ButtonComponent>
      <ButtonComponent variant="primary" @click="buttonTambah"> Tambah Jadwal </ButtonComponent>
    </div>

    <!-- Panel Import Jadwal dari Excel (13 Sep 2026) - toggle, tersembunyi kecuali tombol
    "Import Excel" di atas diklik, biar tampilan halaman tetap ringkas buat pemakaian sehari-hari
    (cuma tambah 1-2 jadwal manual). Pola card & hasil import SAMA PERSIS dengan section "Import
    Soal dari Excel" di BankPertanyaan.vue, biar Admin dapat pengalaman yang konsisten. -->
    <div v-if="show_import_panel" class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <h2 class="text-base font-semibold text-gray-800 mb-1">Import Jadwal dari Excel</h2>
      <p class="text-sm text-gray-500 mb-4">
        Format file .xlsx, .xls, atau .csv, dengan judul kolom (nama_jadwal, area_audit,
        tanggal_awal, tanggal_akhir, semester) di baris pertama. Tanggal boleh format tanggal
        Excel biasa atau teks (mis. "2027-01-31"). Semester diisi kode 5 digit (mis. "20271").
        Baris yang nama+area+semester-nya sudah ada otomatis dilewati (tidak dibuat ulang).
      </p>
      <div class="flex flex-wrap items-center gap-3">
        <label
          for="jadwal-excel-file-input"
          class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-semibold bg-green-700/10 text-green-800 hover:bg-green-700/20 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
          </svg>
          Masukkan File
        </label>
        <input
          id="jadwal-excel-file-input"
          ref="fileInputRef"
          type="file"
          @change="handleFileSelect"
          accept=".xlsx, .xls, .csv"
          class="hidden"
        />
        <span class="text-sm text-gray-500 truncate max-w-[220px]">
          {{ importFile ? importFile.name : 'Belum ada file dipilih' }}
        </span>
        <button
          @click="submitImport"
          :disabled="isImporting || !importFile"
          class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-md text-sm font-medium disabled:opacity-50 transition-colors"
        >
          {{ isImporting ? 'Mengunggah...' : 'Import Excel' }}
        </button>
      </div>

      <div
        v-if="importResult"
        class="mt-4 rounded-md border p-4"
        :class="importResult.berhasil > 0 ? 'border-amber-200 bg-amber-50' : 'border-red-200 bg-red-50'"
      >
        <div class="flex items-start justify-between gap-3">
          <p class="text-sm font-semibold" :class="importResult.berhasil > 0 ? 'text-amber-800' : 'text-red-800'">
            {{ importResult.berhasil ?? 0 }} dari {{ importResult.total_baris ?? '?' }} baris berhasil
            diimport. {{ (importResult.peringatan?.length || 0) + (importResult.info?.length || 0) }}
            baris dilewati - detail:
          </p>
          <button @click="importResult = null" class="text-amber-600 hover:text-amber-800 text-sm shrink-0">
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

    <TableComponent
      :headers="headers"
      @per_page="handlePerPageChange"
      :dataTable="data_table"
      :loading="loader"
      @pagechanged="onPageChange"
      @delete="buttonDelete"
      @edit="buttonEdit"
    ></TableComponent>
  </div>

  <JadwalAuditForm
    v-if="show_form"
    @back="buttonKembali"
    :data="data_awal"
    :tipe="tipe_form"
    @edit="buttonEdit"
  />
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { debounce } from 'lodash'
import axiosClient from '@/axios'
import TableComponent from '@/components/TableComponent.vue'
import ButtonComponent from '@/components/ButtonComponent.vue'
import JadwalAuditForm from './JadwalAuditForm.vue'
import { notifyError } from '@/utils/notify'
import { confirmDialog } from '@/utils/confirmDialog'
// QOL fix (13 Sep 2026, dilaporkan user) - lihat komentar di kolom 'semester_label' pada
// `headers` di atas.
import { formatSemester } from '@/utils/formatSemester'

const route = useRoute()
const router = useRouter()

const filter = reactive({
  status: null,
  filter: null,
  paginate: 25,
})

const data_table = reactive({
  data: [],
  page: 1,
  per_page: 25,
  total: 0,
  last_page: 1,
  from: 1,
  to: 0,
})

const headers = computed(() => [
  { key: 'nama_jadwal', label: 'Nama Jadwal', view: 'title' },
  { key: 'area_audit', label: 'Area Audit' },
  { key: 'tanggal_awal', label: 'Tanggal Awal' },
  { key: 'tanggal_akhir', label: 'Tanggal Akhir' },
  // QOL fix (13 Sep 2026, dilaporkan user) - kolom ini dulu nampilin kode mentah "20272"
  // langsung dari field `semester`. TableComponent cuma render `p[key]` apa adanya (tidak ada
  // formatting bawaan), jadi dibikin field TERPISAH `semester_label` (lihat mapping di
  // getJadwalAudit() di bawah) khusus buat ditampilkan - field `semester` mentahnya SENGAJA
  // tetap dipertahankan apa adanya di tiap baris karena masih dipakai form Edit (Multiselect di
  // JadwalAuditForm.vue butuh kode mentah buat valueProp, bukan label).
  { key: 'semester_label', label: 'Semester' },
  // Tombol "Detail" (ikon mata) DIHAPUS (14 Sep 2026, permintaan user setelah testing E2E) -
  // dulu membuka form yang isinya PERSIS sama dengan "Edit" (termasuk field-nya tetap bisa
  // diedit & disubmit) tapi panel Penugasan Auditor/Auditee di bawahnya nggak pernah kebuka
  // (bug), jadi cuma versi Edit yang lebih terbatas/rusak - bukan tampilan read-only yang
  // beneran berbeda. "Edit" saja sudah cukup buat lihat & kelola jadwal + penugasannya.
  { button: ['Delete', 'Edit'] },
])

const show_form = ref(false)
const tipe_form = ref('create')
const data_awal = ref({})
const show_filter = ref(false)
const show_import_panel = ref(false)
const importFile = ref(null)
const isImporting = ref(false)
const importResult = ref(null)
const fileInputRef = ref(null)
// Mulai true (bukan false) supaya begitu halaman dibuka, tabel langsung nampilin "Memuat data..."
// - bukan "Data Not Found" dulu selama ~1 detik sebelum request pertama sempat jalan (ada debounce
// 1 detik di onMounted sebelum getJadwalAudit() beneran dipanggil pertama kali).
const loader = ref(true)

// --- FUNGSI DIRECT HIT API KE BACKEND ---
// QOL fix (13 Sep 2026, dilaporkan user) - tambahkan field `semester_label` (label manusiawi)
// di tiap baris TANPA mengubah field `semester` mentahnya - field mentah itu masih dipakai form
// Edit (JadwalAuditForm.vue butuh kode 5 digit buat valueProp Multiselect-nya, lihat komentar di
// kolom 'semester_label' pada `headers` di atas).
const tambahSemesterLabel = (rows) => rows.map((row) => ({ ...row, semester_label: formatSemester(row.semester) }))

// --- FUNGSI DIRECT HIT API KE BACKEND ---
const getJadwalAudit = async (page = 1, currentFilter = {}) => {
  loader.value = true
  try {
    const response = await axiosClient.post('/jadwalaudit/data', {
      page: page,
      paginate: currentFilter.paginate,
      filter: currentFilter.filter,
      status: currentFilter.status,
    })

    if (!response || !response.data) {
      console.warn('API gagal atau kosong, menghentikan proses baca data.')
      return
    }

    // PERBAIKAN MAPPING DATA DI SINI
    // Ambil payload murni dari Laravel
    const payload = response.data

    // Cek apakah data dari backend berupa Array (tanpa paginasi) atau Object (dengan paginasi)
    if (Array.isArray(payload)) {
      data_table.data = tambahSemesterLabel(payload)
      data_table.total = payload.length
      data_table.per_page = currentFilter.paginate
      data_table.last_page = 1
      data_table.from = 1
      data_table.to = payload.length
      data_table.page = page
    } else {
      // Jika menggunakan format paginasi dari Laravel ->paginate()
      data_table.data = tambahSemesterLabel(payload.data || [])
      data_table.total = payload.total || 0
      data_table.per_page = payload.per_page || currentFilter.paginate
      data_table.last_page = payload.last_page || 1
      data_table.from = payload.from || 0
      data_table.to = payload.to || 0
      data_table.page = payload.current_page || page
    }
  } catch (error) {
    console.error('Gagal mengambil data jadwal audit:', error)
  } finally {
    loader.value = false
  }
}

const debounceJadwalAudit = debounce(async (page, currentFilter) => {
  await getJadwalAudit(page, currentFilter)
}, 1000)

const buttonTambah = async () => {
  tipe_form.value = 'create'
  show_form.value = true
}

// Import Jadwal Audit dari Excel (13 Sep 2026) - pola sama persis dengan handleFileUpload/
// submitUpload di BankPertanyaan.vue, biar Admin dapat pengalaman yang konsisten antara 2 fitur
// import yang ada di project ini. Lihat JadwalAuditImport.php buat detail aturan per-kolomnya.
const handleFileSelect = (event) => {
  importFile.value = event.target.files[0]
}

const submitImport = async () => {
  if (!importFile.value) {
    notifyError('Pilih file Excel terlebih dahulu!')
    return
  }

  isImporting.value = true
  importResult.value = null
  const formData = new FormData()
  formData.append('file', importFile.value)

  try {
    // Toast sukses ("Berhasil import N Jadwal Audit...") sudah otomatis ditampilkan interceptor
    // axios.js dari `message` yang dibalikin backend.
    const res = await axiosClient.post('/jadwalaudit/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    importResult.value = res.data
    importFile.value = null
    if (fileInputRef.value) fileInputRef.value.value = ''
    data_table.page = 1
    await getJadwalAudit(data_table.page, filter)
  } catch (error) {
    console.error('Error import jadwal:', error)
    notifyError(error.response?.data?.message || 'Gagal import data Excel.')
    // Backend tetap balikin detail peringatan/info walau statusnya "0 data masuk" (400) - lihat
    // JadwalAuditController::importExcel() - jadi tetap ditampilkan kalau ada.
    if (error.response?.data?.peringatan || error.response?.data?.info) {
      importResult.value = error.response.data
    }
  } finally {
    isImporting.value = false
  }
}

const buttonKembali = async () => {
  show_form.value = false
  show_filter.value = false
  filter.paginate = 25
  filter.status = null
  data_awal.value = {}
  data_table.page = 1

  await getJadwalAudit(data_table.page, filter)
}

const buttonEdit = async (data) => {
  show_form.value = true
  tipe_form.value = 'edit'
  data_awal.value = data
}

const buttonDelete = async (data) => {
  // 1. Ambil ID-nya saja (jaga-jaga jika TableComponent melempar seluruh object data)
  const idToDelete = typeof data === 'object' ? data.id : data

  // Modal konfirmasi modular (11 Sep 2026) - gantinya window.confirm() bawaan browser, lihat
  // stores/confirmDialog.js + components/ConfirmDialogComponent.vue.
  if (
    await confirmDialog('Apakah Anda yakin ingin menghapus jadwal ini?', {
      title: 'Hapus Jadwal',
      confirmText: 'Hapus',
      variant: 'danger',
    })
  ) {
    try {
      await axiosClient.post('/jadwalaudit/data/destroy', {
        id: idToDelete, // Mengirimkan ID yang sudah diekstrak
      })

      // Refresh tabel setelah berhasil delete. Toast sukses ("Sukses") sudah otomatis dari
      // interceptor axios.js - dulu ada alert() manual duplikat di sini (dirapikan 10 Sep).
      data_table.page = 1
      await getJadwalAudit(data_table.page, filter)
    } catch (error) {
      console.error('Gagal menghapus data:', error)

      // Menangkap pesan error asli dari Laravel jika gagal dihapus
      if (error.response && error.response.data) {
        notifyError('Gagal menghapus: ' + JSON.stringify(error.response.data))
      } else {
        notifyError('Terjadi kesalahan saat menghapus data.')
      }
    }
  }
}

const filterSearch = debounce(async (event) => {
  if (event.altKey) {
    event.preventDefault()
  } else {
    data_table.page = 1
    await getJadwalAudit(data_table.page, filter)
  }
}, 1000)

const handlePerPageChange = async (limit) => {
  filter.paginate = limit
  data_table.page = 1
  await getJadwalAudit(data_table.page, filter)
}

const onPageChange = async (page) => {
  data_table.page = page
  await getJadwalAudit(data_table.page, filter)
}

onMounted(async () => {
  filter.paginate = 25

  // Deep-link "buka langsung ke jadwal ini" - dipakai dari halaman lain (mis. Auditor/Auditee)
  // lewat link ke /admin/jadwal-audit?edit=<id>. Kalau ada, langsung ambil 1 baris itu dan buka
  // mode Edit, tanpa nunggu user cari & klik Edit manual di daftar.
  const editId = route.query.edit
  if (editId) {
    try {
      const response = await axiosClient.post('/jadwalaudit/data', { id: editId })
      const payload = response?.data
      const record = Array.isArray(payload) ? payload[0] : (payload?.data?.[0] ?? null)
      if (record) {
        await buttonEdit(record)
      } else {
        console.warn('Jadwal dengan id ini tidak ditemukan:', editId)
      }
    } catch (error) {
      console.error('Gagal membuka jadwal lewat deep-link:', error)
    } finally {
      // Bersihin query param dari URL biar nggak nyangkut kalau user reload/back nanti.
      router.replace({ path: route.path })
    }
  }

  await debounceJadwalAudit(data_table.page, filter)
})
</script>

<style scoped></style>
