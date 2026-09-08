<template>
  <div v-if="!show_form">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem">
      <ButtonComponent variant="primary" @click="buttonTambah"> Tambah Jadwal </ButtonComponent>
    </div>

    <TableComponent
      :headers="headers"
      @per_page="handlePerPageChange"
      :dataTable="data_table"
      :loading="loader"
      @pagechanged="onPageChange"
      @delete="buttonDelete"
      @detail="buttonDetail"
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
  { key: 'semester', label: 'Semester' },
  { button: ['Delete', 'Detail', 'Edit'] },
])

const show_form = ref(false)
const tipe_form = ref('create')
const data_awal = ref({})
const show_filter = ref(false)
// Mulai true (bukan false) supaya begitu halaman dibuka, tabel langsung nampilin "Memuat data..."
// - bukan "Data Not Found" dulu selama ~1 detik sebelum request pertama sempat jalan (ada debounce
// 1 detik di onMounted sebelum getJadwalAudit() beneran dipanggil pertama kali).
const loader = ref(true)

// --- FUNGSI DIRECT HIT API KE BACKEND ---
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
      data_table.data = payload
      data_table.total = payload.length
      data_table.per_page = currentFilter.paginate
      data_table.last_page = 1
      data_table.from = 1
      data_table.to = payload.length
      data_table.page = page
    } else {
      // Jika menggunakan format paginasi dari Laravel ->paginate()
      data_table.data = payload.data || []
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

const buttonDetail = async (data) => {
  show_form.value = true
  tipe_form.value = 'detail'
  data_awal.value = data
}

const buttonDelete = async (data) => {
  // 1. Ambil ID-nya saja (jaga-jaga jika TableComponent melempar seluruh object data)
  const idToDelete = typeof data === 'object' ? data.id : data

  if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
    try {
      await axiosClient.post('/jadwalaudit/data/destroy', {
        id: idToDelete, // Mengirimkan ID yang sudah diekstrak
      })

      // Refresh tabel setelah berhasil delete
      data_table.page = 1
      await getJadwalAudit(data_table.page, filter)
      alert('Data berhasil dihapus!')
    } catch (error) {
      console.error('Gagal menghapus data:', error)

      // Menangkap pesan error asli dari Laravel jika gagal dihapus
      if (error.response && error.response.data) {
        alert('Gagal menghapus: ' + JSON.stringify(error.response.data))
      } else {
        alert('Terjadi kesalahan saat menghapus data.')
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
