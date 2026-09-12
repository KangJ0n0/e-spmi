<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <div class="mb-6 border-b border-gray-200 pb-4">
      <h1 class="text-2xl font-bold text-gray-800">Cetak Dokumen Instrumen Audit</h1>
      <p class="text-sm text-gray-500">
        Pilih jadwal, isi Standar &amp; Tipe Audit, lalu download dokumen resmi per instrumen.
        Instrumen 5 (PTK) digenerate PER KATEGORI temuan - bisa sampai 3 dokumen terpisah
        (OBS/Minor/Mayor) tergantung kategori apa saja yang ada di jadwal ini.
      </p>
    </div>

    <!-- GRID KARTU JADWAL - pola auto-fit yang sama seperti JadwalAuditor.vue/JadwalAuditee.vue,
         biar nggak nyisain ruang kosong besar kalau jadwalnya cuma 1-2. -->
    <div v-if="isLoading" class="text-sm text-gray-500">Memuat jadwal...</div>
    <div v-else-if="jadwalList.length === 0" class="text-sm text-gray-500">
      Belum ada jadwal audit.
    </div>
    <div v-else class="grid gap-6 max-w-4xl" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))">
      <div
        v-for="jadwal in jadwalList"
        :key="jadwal.id"
        class="border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow"
        :class="selectedJadwal?.id === jadwal.id ? 'ring-2 ring-blue-500' : ''"
      >
        <h3 class="font-semibold text-gray-800">{{ jadwal.nama_jadwal }}</h3>
        <p class="text-sm text-gray-500">{{ jadwal.area_audit }}</p>
        <p class="text-xs text-gray-400 mt-1">
          {{ formatTanggal(jadwal.tanggal_awal) }} - {{ formatTanggal(jadwal.tanggal_akhir) }}
        </p>
        <button
          @click="bukaPanelCetak(jadwal)"
          class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium shadow-sm transition-colors"
        >
          {{ selectedJadwal?.id === jadwal.id ? 'Sedang Dipilih' : 'Cetak Dokumen' }}
        </button>
      </div>
    </div>

    <!-- PANEL CETAK: muncul di bawah grid begitu 1 jadwal dipilih. -->
    <div
      v-if="selectedJadwal"
      class="mt-8 max-w-2xl border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <div class="flex justify-between items-start mb-4">
        <div>
          <h2 class="text-lg font-bold text-gray-800">{{ selectedJadwal.nama_jadwal }}</h2>
          <p class="text-sm text-gray-500">{{ selectedJadwal.area_audit }}</p>
        </div>
        <button @click="tutupPanel" class="text-sm text-red-500 hover:text-red-700 font-medium">
          X Tutup
        </button>
      </div>

      <!-- STANDAR & TIPE AUDIT: dua field ini TIDAK ADA di database (dikonfirmasi user: diisi
           manual pas generate dokumen), jadi diisi di sini tiap mau cetak, bukan disimpan. -->
      <div class="bg-white border border-gray-200 rounded-md p-4 mb-4 space-y-3">
        <p class="text-xs text-gray-500">
          Dua field di bawah ini nggak disimpan ke database - cuma dipakai buat ngeprint header
          dokumen tiap kali kamu download.
        </p>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Standar</label>
          <input
            v-model="standar"
            type="text"
            placeholder="Contoh: Standar Mahasiswa"
            class="w-full border border-gray-300 rounded-md p-2.5 text-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Audit</label>
          <input
            v-model="tipeAudit"
            type="text"
            placeholder="Contoh: Reguler"
            class="w-full border border-gray-300 rounded-md p-2.5 text-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">
            Divalidasi Oleh <span class="text-gray-400 font-normal">(khusus Instrumen 5 &amp; 6)</span>
          </label>
          <input
            v-model="divalidasi"
            type="text"
            placeholder="Contoh: Cahyaningtyas Ria Uripi, S.E., M.Si."
            class="w-full border border-gray-300 rounded-md p-2.5 text-sm"
          />
        </div>
      </div>

      <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Download Per Instrumen</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <button
          v-for="instrumen in instrumenTersedia"
          :key="instrumen.nomor"
          @click="downloadInstrumen(instrumen.nomor)"
          :disabled="downloading[instrumen.nomor]"
          class="bg-white border border-gray-300 rounded-md p-3 text-left hover:border-blue-500 hover:shadow-sm transition-all disabled:opacity-50"
        >
          <p class="text-sm font-semibold text-gray-700">Instrumen {{ instrumen.nomor }}</p>
          <p class="text-[11px] text-gray-400">{{ instrumen.label }}</p>
          <p class="text-[11px] text-blue-600 font-medium mt-1">
            {{ downloading[instrumen.nomor] ? 'Menyiapkan PDF...' : 'Download PDF' }}
          </p>
        </button>
      </div>

      <!-- Instrumen 5 (PTK) - BEDA dari instrumen lain: digenerate PER KATEGORI temuan, jadi
           tombolnya 3 (OBS/Minor/Mayor), bukan 1. Klik masing-masing buat download dokumen
           kategori itu terpisah. -->
      <p class="text-xs font-semibold text-gray-400 uppercase mt-4 mb-2">
        Instrumen 5 - Tindak Lanjut KTS (PTK), per Kategori Temuan
      </p>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <button
          v-for="kategori in kategoriTemuanList"
          :key="kategori.value"
          @click="downloadInstrumen(5, kategori.value)"
          :disabled="downloading['5-' + kategori.value]"
          class="bg-white border border-gray-300 rounded-md p-3 text-left hover:border-blue-500 hover:shadow-sm transition-all disabled:opacity-50"
        >
          <p class="text-sm font-semibold text-gray-700">PTK - {{ kategori.label }}</p>
          <p class="text-[11px] text-gray-400">Kategori Temuan: {{ kategori.value }}</p>
          <p class="text-[11px] text-blue-600 font-medium mt-1">
            {{ downloading['5-' + kategori.value] ? 'Menyiapkan PDF...' : 'Download PDF' }}
          </p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'
import { notifyError } from '@/utils/notify'

const route = useRoute()
const jadwalList = ref([])
const isLoading = ref(false)
const selectedJadwal = ref(null)
const standar = ref('')
const tipeAudit = ref('')
const divalidasi = ref('')
const downloading = ref({})

// Instrumen 5 SENGAJA ditaruh terpisah dari grid ini (lihat blok "Instrumen 5" khusus di
// template) - dokumennya digenerate PER KATEGORI temuan (OBS/Minor/Mayor), jadi butuh 3 tombol,
// bukan 1 seperti instrumen lain. Lihat DokumenAuditController.php buat detail resolusinya.
const instrumenTersedia = [
  { nomor: 1, label: 'Check List' },
  { nomor: 2, label: 'Hasil Audit Lapangan (HAL)' },
  { nomor: 3, label: 'HAL - Kesesuaian (KS)' },
  { nomor: 4, label: 'HAL - Ketidaksesuaian (KTS)' },
  { nomor: 6, label: 'Tindak Lanjut KS (PTP)' },
]

// Sama persis dengan pilihan kategori_temuan di form penilaian Auditor (NilaiInstrumenAuditor.vue)
// - Instrumen 5 nawarin 1 tombol download per kategori di sini.
const kategoriTemuanList = [
  { value: 'OBS', label: 'Observasi (OBS)' },
  { value: 'MINOR', label: 'Minor' },
  { value: 'MAYOR', label: 'Mayor' },
]

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject) promise-nya
// untuk error 400/404/422/500 (termasuk error blob JSON, sudah di-handle di axios.js) - jadi cek
// bentuk response-nya, sama seperti pola di NilaiInstrumenAuditor.vue.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const formatTanggal = (tgl) => {
  if (!tgl) return '-'
  return new Date(tgl).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const fetchJadwal = async () => {
  isLoading.value = true
  if (route.meta.isAdmin) {
    const res = await axiosClient.post('/jadwalaudit/data', {})
    isLoading.value = false
    if (gagal(res)) return
    jadwalList.value = res.data
  } else {
    const res = await axiosClient.get('/auditor/jadwal-saya')
    isLoading.value = false
    if (gagal(res)) return
    // Normalisasi: endpoint ini balikin `jadwal_spmi_id`, bukan `id`, biar konsisten dengan
    // sumber data Admin di atas.
    jadwalList.value = res.data.map((j) => ({
      id: j.jadwal_spmi_id,
      nama_jadwal: j.nama_jadwal,
      area_audit: j.area_audit,
      tanggal_awal: j.tanggal_awal,
      tanggal_akhir: j.tanggal_akhir,
      semester: j.semester,
    }))
  }
}

const bukaPanelCetak = (jadwal) => {
  selectedJadwal.value = jadwal
  standar.value = ''
  tipeAudit.value = ''
  divalidasi.value = ''
}

const tutupPanel = () => {
  selectedJadwal.value = null
}

const downloadInstrumen = async (nomor, kategoriTemuan = null) => {
  if (!standar.value.trim() || !tipeAudit.value.trim()) {
    notifyError('Isi Standar dan Tipe Audit dulu sebelum download.')
    return
  }
  // Instrumen 5 (PTK) & 6 (PTP) punya 1 field tambahan wajib: DIVALIDASI (lihat DokumenAuditController.php).
  if ((nomor === 5 || nomor === 6) && !divalidasi.value.trim()) {
    notifyError(`Isi "Divalidasi Oleh" dulu sebelum download Instrumen ${nomor}.`)
    return
  }

  // Key unik per tombol - Instrumen 5 punya 3 tombol (per kategori) yang harus bisa loading
  // sendiri-sendiri, instrumen lain cukup nomornya sebagai key.
  const key = nomor === 5 ? `5-${kategoriTemuan}` : String(nomor)

  downloading.value = { ...downloading.value, [key]: true }
  const res = await axiosClient.get(`/jadwal-audit/${selectedJadwal.value.id}/dokumen/${nomor}`, {
    params: {
      standar: standar.value,
      tipe_audit: tipeAudit.value,
      divalidasi: divalidasi.value,
      kategori_temuan: kategoriTemuan,
    },
    responseType: 'blob',
  })
  downloading.value = { ...downloading.value, [key]: false }

  if (gagal(res)) return

  const url = window.URL.createObjectURL(res.data)
  const link = document.createElement('a')
  link.href = url
  const namaKategori = nomor === 5 ? `-${kategoriTemuan}` : ''
  link.download = `Instrumen-${nomor}${namaKategori}-${selectedJadwal.value.nama_jadwal}-${selectedJadwal.value.area_audit}.pdf`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}

onMounted(() => {
  fetchJadwal()
})
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
