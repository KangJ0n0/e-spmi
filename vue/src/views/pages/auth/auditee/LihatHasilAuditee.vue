<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Lihat Hasil Evaluasi</h1>
        <p class="text-sm text-gray-500">
          Cek jawaban yang sudah Anda kirim beserta hasil penilaian Auditor (KS/KTS).
        </p>
      </div>
      <router-link
        to="/auditee/jadwal-auditee"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR PERTANYAAN (read-only, semua status) -->
    <div v-if="!soalAktif">
      <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
              Butir Standar
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
            v-for="(item, index) in pagedPertanyaan"
            :key="item.id"
            v-else
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ (halaman - 1) * perHalaman + index + 1 }}</td>
            <td class="px-4 py-3 text-sm text-gray-800">
              <div class="font-medium" v-html="formatTeksBernomor(item.pertanyaan?.butir_pertanyaan)"></div>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                v-if="!item.jawaban?.deskripsi_hasil"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500"
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
                v-if="item.jawaban?.deskripsi_hasil"
                @click="soalAktif = item"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-300"
              >
                Lihat Detail
              </button>
              <router-link
                v-else
                :to="{ path: '/auditee/instrumen-auditee', query: { id: jadwalId } }"
                class="text-xs font-medium text-blue-600 hover:text-blue-800"
              >
                Isi jawaban dulu
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
      <Pagination :page="halaman" :total-pages="totalHalaman" @update:page="halaman = $event" />
    </div>

    <!-- TAMPILAN 2: DETAIL HASIL (read-only) - jawaban Auditee sendiri + hasil penilaian Auditor
         (KS/KTS) kalau sudah dinilai. Markup ini sengaja mirroring "Lihat Hasil" milik Auditor di
         NilaiInstrumenAuditor.vue (dipindah dari IsiInstrumenAuditee.vue 10 Sep, biar nggak numpuk
         di halaman yang sama dengan form Jawab Pertanyaan - dilaporkan user). -->
    <div
      v-else
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- Konteks: Instrumen 1 (butir soal) -->
      <div class="mb-6 bg-white p-4 border border-blue-100 rounded-md shadow-sm">
        <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px] shrink-0">1</span>
          Butir Soal
        </h3>
        <div class="text-sm text-gray-700 font-medium mb-1" v-html="formatTeksBernomor(soalAktif.pertanyaan?.pertanyaan)"></div>
        <div class="text-sm text-gray-600">
          Butir: <span v-html="formatTeksBernomor(soalAktif.pertanyaan?.butir_pertanyaan)"></span>
        </div>
      </div>

      <!-- Jawaban Auditee sendiri (Instrumen 2), read-only - KECUALI lagi mode revisi (tombol
           "Revisi Jawaban" DIPINDAH 1 Okt 2026 ke baris bawah bareng "Kembali ke Daftar", lihat
           komentar di situ - biar nggak numpuk di header kotak ini). -->
      <div class="mb-6 bg-blue-50 p-4 border border-l-4 border-l-blue-500 rounded-md">
        <h3 class="flex items-center gap-2 text-xs font-bold text-blue-800 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] shrink-0">2</span>
          Jawaban &amp; Bukti Dokumen Anda
        </h3>

        <DeskripsiHasilComponent v-if="!modeRevisi" :text="soalAktif.jawaban?.deskripsi_hasil" />

        <!-- Form revisi - dibuka dari tombol di atas, prefill dari jawaban lama (dipisah lagi
             jadi 2 field lewat MARKER_LINK_BUKTI, sama persis polanya kayak yang dipakai
             DeskripsiHasilComponent.vue/NilaiInstrumenAuditor.vue). -->
        <form v-else @submit.prevent="submitRevisi" class="space-y-3">
          <p class="text-xs text-orange-700 bg-orange-50 border border-orange-200 rounded-md p-2">
            Perbaiki jawaban dan/atau link bukti dokumen Anda, lalu kirim ulang. Kalau pertanyaan
            ini sudah pernah dinilai Auditor, penilaiannya akan ditinjau ulang setelah revisi ini.
          </p>
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Jawaban / Kondisi Saat Ini</label>
            <textarea
              v-model="formRevisi.jawaban"
              rows="4"
              class="w-full border border-gray-300 rounded-md p-2 text-sm"
              required
            ></textarea>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Link Bukti Dokumen (Google Drive)</label>
            <input
              type="url"
              v-model="formRevisi.link_bukti"
              class="w-full border border-gray-300 rounded-md p-2 text-sm"
              required
            />
          </div>
          <div class="flex justify-end gap-2 pt-1">
            <button
              type="button"
              @click="modeRevisi = false"
              class="px-3 py-1.5 text-xs border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmittingRevisi"
              class="px-3 py-1.5 text-xs bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50"
            >
              {{ isSubmittingRevisi ? 'Mengirim...' : 'Kirim Revisi ke Auditor' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Belum dinilai Auditor -->
      <div
        v-if="!soalAktif.jawaban?.status_temuan"
        class="bg-white p-5 border border-dashed border-gray-300 rounded-md text-center"
      >
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700">
          Menunggu Penilaian Auditor
        </span>
        <p class="text-sm text-gray-500 mt-2">
          Jawaban Anda sudah terkirim, Auditor belum menentukan status KS/KTS-nya.
        </p>
      </div>

      <!-- Sudah dinilai Auditor: tampilkan hasil KS/KTS + rekomendasi/rencana tindak lanjut. -->
      <div v-else class="bg-white p-5 border border-gray-200 rounded-md">
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
          <span
            class="px-3 py-1 rounded-full text-sm font-bold"
            :class="
              soalAktif.jawaban?.status_temuan === 'KS'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700'
            "
          >
            {{ soalAktif.jawaban?.status_temuan }}
          </span>
          <h3 class="text-sm font-bold text-gray-700">Hasil Penilaian Auditor</h3>
        </div>

        <!-- Notif "Anda sudah mengirim revisi jawaban..." DICABUT (1 Okt 2026, permintaan user:
             "ga perlu notif sudah direvisi dll") - lihat catatan di
             JawabanController::storeAuditee(). -->

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <template v-if="soalAktif.jawaban?.status_temuan === 'KS'">
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Faktor Pendukung
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.faktor_pendukung)"></div>
            </div>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Rencana Peningkatan
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.rencana_peningkatan)"></div>
            </div>
          </template>
          <template v-else>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Kategori Temuan
              </p>
              <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.kategori_temuan }}</p>
            </div>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Akar Penyebab / Faktor Penghambat
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.faktor_penghambat)"></div>
            </div>
            <div class="bg-gray-50 rounded-md p-3 sm:col-span-2">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Rencana Perbaikan
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.rencana_perbaikan)"></div>
            </div>
          </template>

          <div class="bg-gray-50 rounded-md p-3 sm:col-span-2">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">Rekomendasi</p>
            <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.rekomendasi)"></div>
          </div>
          <div class="bg-gray-50 rounded-md p-3">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
              Jadwal Penyelesaian
            </p>
            <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.jadwal_penyelesaian }}</p>
          </div>
          <div class="bg-gray-50 rounded-md p-3">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
              Pihak Bertanggung Jawab
            </p>
            <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.pihak_tanggung_jawab }}</p>
          </div>
        </div>
      </div>

      <!-- Fitur baru (30 Sep 2026), DIPERLUAS (1 Okt 2026), DIPINDAH (1 Okt 2026) - tombol
           "Revisi Jawaban" dulu di header kotak Jawaban & Bukti Dokumen di atas, sekarang
           digabung 1 baris dengan "Kembali ke Daftar" di sini, posisinya di KIRI (permintaan
           user). "Kembali ke Daftar" dipakai `ml-auto` (bukan `justify-end` di containernya)
           biar tetap nempel di kanan walaupun "Revisi Jawaban"-nya lagi nggak muncul (soal belum
           pernah dijawab / lagi mode revisi). Gaya tombol diganti (1 Okt 2026) dari teks
           underline polos jadi tombol beneran (border + background) - sebelumnya dilaporkan
           "kurang jelas dan rapi". -->
      <div class="flex items-center mt-6 pt-4 border-t border-gray-200">
        <button
          v-if="soalAktif.jawaban?.deskripsi_hasil && !modeRevisi"
          type="button"
          @click="bukaRevisi"
          class="px-4 py-2 border border-orange-300 text-orange-700 bg-orange-50 rounded-md text-sm font-medium hover:bg-orange-100"
        >
          Revisi Jawaban
        </button>
        <button
          type="button"
          @click="soalAktif = null"
          class="ml-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100"
        >
          Kembali ke Daftar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'
import DeskripsiHasilComponent from '@/components/DeskripsiHasilComponent.vue'
import Pagination from '@/components/Pagination.vue'
import { usePagination } from '@/composables/usePagination'
import { formatTeksBernomor } from '@/utils/formatTeksBernomor'

const route = useRoute()
const jadwalId = route.query.id

const listPertanyaan = ref([])
const soalAktif = ref(null)
const isLoading = ref(false)

// Pagination (1 Okt 2026) - 20 baris per halaman di tabel daftar pertanyaan.
const { page: halaman, totalPages: totalHalaman, pagedItems: pagedPertanyaan, perPage: perHalaman } =
  usePagination(listPertanyaan, 20)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const fetchListPertanyaan = async () => {
  isLoading.value = true
  const res = await axiosClient.get(`/jadwal-audit/${jadwalId}/pertanyaan`)
  isLoading.value = false
  if (gagal(res)) return
  listPertanyaan.value = res.data
}

// ============================================================
// REVISI JAWABAN - fitur baru (30 Sep 2026), DIPERLUAS (1 Okt 2026)
// ============================================================
// Dulu deskripsi_hasil TERKUNCI PERMANEN begitu sekali diisi, gak peduli hasil penilaiannya
// apa. Sempat direvisi jadi "boleh revisi KALAU sudah ada temuan KTS" saja. User sekarang minta
// scope-nya diperluas lagi: Auditee boleh revisi jawaban utk SEMUA pertanyaan yang SUDAH PERNAH
// DIJAWAB sebelumnya, apa pun status penilaiannya (belum dinilai/KS/KTS) - lihat tombol "Revisi
// Jawaban" di template & JawabanController::storeAuditee() (gate status_temuan sudah dihapus).
// Dipisah dari IsiInstrumenAuditee.vue (form isi pertama kali) karena beda konteks - di sini
// Auditee lagi LIHAT hasil/status jawabannya dulu, baru mutusin revisi, bukan isi form kosong
// dari awal.
const modeRevisi = ref(false)
const isSubmittingRevisi = ref(false)
const formRevisi = reactive({ jawaban: '', link_bukti: '' })

// Sama persis pola pemisahnya kayak DeskripsiHasilComponent.vue & NilaiInstrumenAuditor.vue -
// deskripsi_hasil tersimpan gabungan "<jawaban>\n\nLink Bukti Dokumen: <url>" dari storeAuditee().
const MARKER_LINK_BUKTI = '\n\nLink Bukti Dokumen: '
const pisahDeskripsiHasil = (text) => {
  const idx = (text || '').indexOf(MARKER_LINK_BUKTI)
  if (idx < 0) return { jawaban: text || '', link_bukti: '' }
  return {
    jawaban: text.slice(0, idx),
    link_bukti: text.slice(idx + MARKER_LINK_BUKTI.length),
  }
}

const bukaRevisi = () => {
  const { jawaban, link_bukti } = pisahDeskripsiHasil(soalAktif.value.jawaban?.deskripsi_hasil)
  formRevisi.jawaban = jawaban
  formRevisi.link_bukti = link_bukti
  modeRevisi.value = true
}

const submitRevisi = async () => {
  isSubmittingRevisi.value = true
  try {
    // QOL fix (12 Sep 2026, pola sama seperti fetch di atas) - dibungkus try/finally.
    const res = await axiosClient.post('/auditee/jawaban/store', {
      jadwal_spmi_id: jadwalId,
      pertanyaan_id: soalAktif.value.pertanyaan_id, // bank_pertanyaans.id, sama seperti IsiInstrumenAuditee.vue
      jawaban: formRevisi.jawaban,
      link_bukti: formRevisi.link_bukti,
    })
    if (gagal(res)) return

    // Toast sukses sudah otomatis dari interceptor axios.js.
    modeRevisi.value = false
    await fetchListPertanyaan()
    // soalAktif ikut diganti ke versi terbaru dari list yang baru di-fetch, biar tampilan detail
    // (deskripsi_hasil, dst) langsung kepakai data baru tanpa perlu Auditee klik "Kembali ke
    // Daftar" dulu.
    const idAktif = soalAktif.value.id
    soalAktif.value = listPertanyaan.value.find((i) => i.id === idAktif) || null
  } finally {
    isSubmittingRevisi.value = false
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
