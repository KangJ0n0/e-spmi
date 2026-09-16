<!--
  Halaman BARU (13 Sep 2026): Admin atur Nomor Dokumen Instrumen 1-6 PER PERIODE (semester) -
  bukan per jadwal, karena 1 periode bisa punya BANYAK jadwal dan client konfirmasi nomornya
  kemungkinan SAMA selama 1 periode (lihat docblock lengkap di migration
  create_konfigurasi_nomor_dokumen_table & DokumenAuditController::resolveNomorDokumen()).

  REVISI (13 Sep 2026, sore): field semester AWALNYA <input> + <datalist> ketik manual (kode
  mentah kayak "20272") - user komplain nggak enak dipakai, maunya langsung pilih dari daftar
  siap pakai kayak "Ganjil 2025/2026". Sekarang diganti jadi <select> MURNI, opsinya di-generate
  otomatis lewat computed opsiSemester() - tidak ada lagi ketik manual sama sekali (dikonfirmasi
  user: "Full dropdown saja").

  PENTING biar nggak salah paham: daftar pilihan di dropdown ini SAMA SEKALI TIDAK bergantung ke
  Jadwal Audit yang sudah/belum dibuat - dropdown-nya cuma nge-generate kombinasi Ganjil/Genap
  untuk rentang tahun (lihat RENTANG_TAHUN_SEBELUM/SESUDAH di bawah), independen dari ada/tidaknya
  Jadwal. Admin BOLEH isi nomor dokumen buat semester yang Jadwal Audit-nya belum pernah dibuat
  sama sekali (nyiapin duluan) - begitu nanti ada Jadwal dibuat dengan semester yang sama, otomatis
  "nyambung" ke konfigurasi yang sudah diisi (lihat DokumenAuditController::resolveNomorDokumen()).
-->
<template>
  <div>
    <p class="text-sm text-gray-500 mb-6">
      Atur Nomor Dokumen Instrumen 1-6 per periode (semester). Nomor yang disimpan di sini otomatis
      dipakai untuk SEMUA jadwal audit di semester yang sama - tidak perlu diisi ulang per jadwal.
      Instrumen yang belum diisi di sini tetap memakai nomor bawaan sistem.
    </p>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mb-6">
      <h3 class="text-base font-semibold text-gray-800 mb-4">
        {{ editingId ? 'Edit Konfigurasi' : 'Tambah Konfigurasi Baru' }}
      </h3>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="max-w-xs">
          <label class="block text-sm font-medium text-gray-700 mb-1">Semester / Periode</label>
          <select
            v-model="form.semester"
            required
            :disabled="!!editingId"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
          >
            <option value="" disabled>Pilih periode...</option>
            <option v-for="opsi in opsiSemester" :key="opsi.value" :value="opsi.value">
              {{ opsi.label }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="i in 6" :key="i">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nomor Dokumen Instrumen {{ i }}
            </label>
            <input
              v-model="form['nomor_dokumen_' + i]"
              type="text"
              :placeholder="''"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>

        <div class="flex items-center gap-3">
          <button
            type="submit"
            :disabled="isSaving"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50"
          >
            {{ isSaving ? 'Menyimpan...' : editingId ? 'Simpan Perubahan' : 'Tambah Konfigurasi' }}
          </button>
          <button
            v-if="editingId"
            type="button"
            @click="batalEdit"
            class="text-sm text-gray-500 hover:text-gray-700 px-2"
          >
            Batal
          </button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <h3 class="text-base font-semibold text-gray-800 mb-4">Konfigurasi Tersimpan</h3>

      <div v-if="isLoading" class="text-sm text-gray-500 text-center py-6">Memuat data...</div>
      <div
        v-else-if="daftarKonfigurasi.length === 0"
        class="text-sm text-gray-500 text-center py-6"
      >
        Belum ada konfigurasi. Tambahkan lewat form di atas.
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="item in daftarKonfigurasi"
          :key="item.id"
          class="border border-gray-200 rounded-md p-4"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-gray-800">{{
              formatLabelSemester(item.semester)
            }}</span>
            <div class="flex items-center gap-3">
              <button
                @click="mulaiEdit(item)"
                class="text-blue-600 hover:text-blue-800 text-xs font-medium"
              >
                Edit
              </button>
              <button
                @click="hapusKonfigurasi(item)"
                class="text-red-600 hover:text-red-800 text-xs font-medium"
              >
                Hapus
              </button>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-gray-600">
            <div v-for="i in 6" :key="i">
              <span class="font-medium">Instrumen {{ i }}:</span>
              {{ item['nomor_dokumen_' + i] || '(pakai nomor bawaan)' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axiosClient from '@/axios'
import { confirmDialog } from '@/utils/confirmDialog'

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject) promise-nya
// untuk error 400/404/422/500 - jadi cek bentuk response-nya, bukan cuma try/catch. Pola sama
// persis dengan KategoriInstrumenModal.vue.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

// Berapa tahun ke belakang/depan yang di-generate ke dropdown, dihitung dari tahun berjalan
// (bukan hardcode tahun tetap, biar dropdown-nya nggak "kadaluarsa" tiap tahun berganti).
const RENTANG_TAHUN_SEBELUM = 2
const RENTANG_TAHUN_SESUDAH = 3

// ASUMSI format kode semester (sama persis dengan DokumenAuditController::formatPeriodeAudit() di
// backend - lihat catatan di sana) - 4 digit tahun ajaran mulai + 1 digit tipe (1=Ganjil/2=Genap).
const formatLabelSemester = (semester) => {
  if (!semester || String(semester).length !== 5 || !/^\d+$/.test(semester)) {
    return semester ? `Semester ${semester}` : ''
  }
  const tahun = parseInt(semester.slice(0, 4), 10)
  const tipe = semester.slice(4, 5)
  if (tipe === '1') return `Ganjil ${tahun}/${tahun + 1}`
  if (tipe === '2') return `Genap ${tahun}/${tahun + 1}`
  return `Semester ${semester}`
}

// Generate pilihan dropdown Ganjil/Genap untuk rentang tahun di atas - SAMA SEKALI TIDAK
// bergantung ke Jadwal Audit (lihat catatan panjang di docblock atas template), murni kombinasi
// tahun x tipe semester supaya Admin tinggal pilih, nggak perlu ketik/inget kode mentahnya.
//
// Konfigurasi LAMA yang semesternya sudah lewat dari rentang (mis. beberapa tahun kemudian rentang
// dropdown sudah geser maju) tetap ditambahkan manual ke opsi - biar tombol "Edit" pada konfigurasi
// itu nggak jadi nampilin dropdown kosong/nyasar ke opsi lain gara-gara value-nya nggak ketemu.
const opsiSemester = computed(() => {
  const tahunSekarang = new Date().getFullYear()
  const opsi = []
  const sudahAda = new Set()
  for (
    let tahun = tahunSekarang - RENTANG_TAHUN_SEBELUM;
    tahun <= tahunSekarang + RENTANG_TAHUN_SESUDAH;
    tahun++
  ) {
    ;[`${tahun}1`, `${tahun}2`].forEach((kode) => {
      opsi.push({ value: kode, label: formatLabelSemester(kode) })
      sudahAda.add(kode)
    })
  }
  daftarKonfigurasi.value
    .map((item) => item.semester)
    .filter((semester) => semester && !sudahAda.has(semester))
    .forEach((semester) => {
      opsi.push({ value: semester, label: formatLabelSemester(semester) + ' (lama)' })
      sudahAda.add(semester)
    })
  return opsi
})

const kosongkanForm = () => ({
  semester: '',
  nomor_dokumen_1: '',
  nomor_dokumen_2: '',
  nomor_dokumen_3: '',
  nomor_dokumen_4: '',
  nomor_dokumen_5: '',
  nomor_dokumen_6: '',
})

const form = ref(kosongkanForm())
const editingId = ref(null)
const isSaving = ref(false)
const isLoading = ref(true)
const errorMsg = ref('')
const daftarKonfigurasi = ref([])

const fetchKonfigurasi = async () => {
  isLoading.value = true
  try {
    const res = await axiosClient.get('/konfigurasi-nomor-dokumen')
    if (gagal(res)) return
    daftarKonfigurasi.value = res.data
  } finally {
    isLoading.value = false
  }
}

const mulaiEdit = (item) => {
  editingId.value = item.id
  form.value = {
    semester: item.semester,
    nomor_dokumen_1: item.nomor_dokumen_1 || '',
    nomor_dokumen_2: item.nomor_dokumen_2 || '',
    nomor_dokumen_3: item.nomor_dokumen_3 || '',
    nomor_dokumen_4: item.nomor_dokumen_4 || '',
    nomor_dokumen_5: item.nomor_dokumen_5 || '',
    nomor_dokumen_6: item.nomor_dokumen_6 || '',
  }
  errorMsg.value = ''
}

const batalEdit = () => {
  editingId.value = null
  form.value = kosongkanForm()
  errorMsg.value = ''
}

const submitForm = async () => {
  isSaving.value = true
  errorMsg.value = ''
  try {
    // Backend upsert by semester (lihat KonfigurasiNomorDokumenController::store) - store() dipakai
    // buat tambah BARU maupun edit yang sudah ada, jadi tinggal 1 endpoint buat 2 kasus.
    const res = await axiosClient.post('/konfigurasi-nomor-dokumen', form.value)
    if (gagal(res)) {
      errorMsg.value =
        res.response?.data?.errors?.semester?.[0] ||
        res.response?.data?.message ||
        'Gagal menyimpan konfigurasi.'
      return
    }

    batalEdit()
    await fetchKonfigurasi()
  } finally {
    isSaving.value = false
  }
}

const hapusKonfigurasi = async (item) => {
  const ok = await confirmDialog(
    `Hapus konfigurasi nomor dokumen untuk ${formatLabelSemester(item.semester)}? Jadwal di semester ini akan kembali memakai nomor dokumen bawaan sistem.`,
    { title: 'Hapus Konfigurasi Nomor Dokumen', confirmText: 'Hapus', variant: 'danger' },
  )
  if (!ok) return

  const res = await axiosClient.delete(`/konfigurasi-nomor-dokumen/${item.id}`)
  if (gagal(res)) return

  await fetchKonfigurasi()
}

onMounted(() => {
  fetchKonfigurasi()
})
</script>
