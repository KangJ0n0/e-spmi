<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Evaluasi Diri (Auditee)</h1>
        <p class="text-sm text-gray-500">
          Isi jawaban dan lampirkan link bukti dokumen (Google Drive) untuk tiap butir
          pertanyaan.
        </p>
      </div>
      <router-link
        to="/auditee/evaluasi-diri"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR PERTANYAAN -->
    <div v-if="!modeIsiForm" class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
              Butir Standar &amp; Dokumen
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Status
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Memuat...</td>
          </tr>
          <tr v-else-if="listPertanyaan.length === 0">
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
              Belum ada pertanyaan yang dikirim Auditor untuk jadwal ini.
            </td>
          </tr>
          <tr
            v-for="(item, index) in listPertanyaan"
            :key="item.id"
            v-else
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ index + 1 }}</td>
            <td class="px-4 py-3 text-sm text-gray-800">
              <div class="font-medium mb-1" v-html="formatTeksBernomor(item.pertanyaan?.butir_pertanyaan)"></div>
              <p
                v-if="item.pertanyaan?.dokumen_cek"
                class="text-xs text-gray-500 bg-gray-100 p-1 rounded inline-block"
              >
                Bukti: {{ item.pertanyaan.dokumen_cek }}
              </p>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                v-if="!item.jawaban?.deskripsi_hasil"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700"
              >
                Belum Diisi
              </span>
              <span
                v-else-if="item.jawaban?.status_temuan"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700"
              >
                Sudah Dinilai ({{ item.jawaban.status_temuan }})
              </span>
              <span
                v-else
                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700"
              >
                Menunggu Penilaian Auditor
              </span>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <button
                v-if="!item.jawaban?.deskripsi_hasil"
                @click="bukaForm(item)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium shadow-sm"
              >
                Isi Jawaban
              </button>
              <!-- Dulu tombol ini lompat ke halaman "Lihat Hasil" terpisah (10 Sep). Dihapus lagi
              (11 Sep, lanjutan) atas permintaan user - "Evaluasi Diri" dan "Lihat Hasil" sekarang
              2 menu sidebar terpisah, jadi soal yang sudah dijawab di halaman ini cukup tampil
              badge status saja (di kolom Status), TANPA tombol lompat ke Lihat Hasil - biar tidak
              tumpang tindih sama menu "Lihat Hasil" yang sudah ada sendiri. -->
              <span v-else class="text-xs text-gray-400 italic">Terkirim</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TAMPILAN 2: FORM PENGISIAN AUDITEE -->
    <div
      v-else-if="modeIsiForm"
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- Box Info Pertanyaan -->
      <div class="mb-6 bg-white p-5 border border-l-4 border-l-blue-500 rounded-md shadow-sm">
        <h3 class="text-sm font-bold text-blue-800 mb-2">Pernyataan Standar:</h3>
        <div class="text-sm text-gray-700 font-medium mb-3" v-html="formatTeksBernomor(soalAktif.pertanyaan?.pertanyaan)"></div>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Butir Pertanyaan:</h3>
        <div class="text-sm text-gray-700 mb-3" v-html="formatTeksBernomor(soalAktif.pertanyaan?.butir_pertanyaan)"></div>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Dokumen yang Harus Disiapkan:</h3>
        <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.pertanyaan?.dokumen_cek)"></div>
      </div>

      <!-- Form Input Auditee -->
      <form @submit.prevent="submitJawaban">
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1"
              >Jawaban / Kondisi Saat Ini <span class="text-red-500">*</span></label
            >
            <p class="text-xs text-gray-500 mb-2">
              Jelaskan kondisi riil pemenuhan standar ini di unit Anda.
            </p>
            <textarea
              v-model="form.jawaban"
              rows="5"
              placeholder="Contoh: Kami telah melaksanakan perkuliahan 14 kali pertemuan sesuai RPS..."
              class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
              required
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1"
              >Link Bukti Dokumen (Google Drive) <span class="text-red-500">*</span></label
            >
            <p class="text-xs text-gray-500 mb-2">
              Masukkan link Google Drive / Cloud yang berisi dokumen bukti yang diminta.
            </p>
            <input
              type="url"
              v-model="form.link_bukti"
              placeholder="https://drive.google.com/drive/folders/..."
              class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="batalIsi"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-5 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Kirim ke Auditor' }}
          </button>
        </div>
      </form>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'
import { formatTeksBernomor } from '@/utils/formatTeksBernomor'

const route = useRoute()
const jadwalId = route.query.id

const listPertanyaan = ref([])
const modeIsiForm = ref(false)
const soalAktif = ref(null)
const isLoading = ref(false)
const isSubmitting = ref(false)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const form = reactive({
  jadwal_spmi_id: jadwalId,
  pertanyaan_id: '',
  jawaban: '',
  link_bukti: '',
})

const fetchListPertanyaan = async () => {
  isLoading.value = true
  try {
    // QOL fix (12 Sep 2026) - dibungkus try/finally. Dulu kalau axios BENERAN reject (network
    // error/timeout - lihat komentar "gagal()" di atas, bukan error 400/404/422/500/401/403 yang
    // di-resolve interceptor), baris `isLoading.value = false` di bawah tidak pernah kesampaian
    // sama sekali karena `await` di atasnya melempar exception - tombol/tabel jadi kekunci status
    // "Memuat..." SELAMANYA sampai halaman di-reload manual.
    const res = await axiosClient.get(`/jadwal-audit/${jadwalId}/pertanyaan`)
    if (gagal(res)) return
    listPertanyaan.value = res.data
  } finally {
    isLoading.value = false
  }
}

const bukaForm = (item) => {
  soalAktif.value = item
  form.pertanyaan_id = item.pertanyaan_id
  form.jawaban = ''
  form.link_bukti = ''
  modeIsiForm.value = true
}

const batalIsi = () => {
  modeIsiForm.value = false
  soalAktif.value = null
}

// "Lihat Hasil" (klik pertanyaan yang sudah dijawab) sekarang pindah ke halaman terpisah
// LihatHasilAuditee.vue (10 Sep) - lihat router-link di TAMPILAN 1 di atas, nggak lagi toggle
// state lokal di sini.

const submitJawaban = async () => {
  isSubmitting.value = true
  try {
    // QOL fix (12 Sep 2026) - sama seperti fetchListPertanyaan() di atas, dibungkus try/finally
    // biar tombol "Menyimpan..." tidak kekunci permanen kalau network error.
    const res = await axiosClient.post('/auditee/jawaban/store', form)
    if (gagal(res)) return

    // Toast sukses sudah otomatis dari interceptor axios.js (backend balikin `message`) - dulu
    // ada alert() manual duplikat di sini (dirapikan 10 Sep, lihat src/utils/notify.js).
    modeIsiForm.value = false
    await fetchListPertanyaan()
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchListPertanyaan()
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
