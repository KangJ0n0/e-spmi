<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Revisi Jawaban (Auditee)</h1>
        <p class="text-sm text-gray-500">
          Perbaiki jawaban dan/atau link bukti dokumen untuk pertanyaan yang sudah Anda kirim.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="soalAktif"
          type="button"
          @click="tutupRevisi"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
        >
          Kembali ke Daftar Pertanyaan
        </button>
        <router-link
          to="/auditee/revisi"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
        >
          Kembali ke Jadwal
        </router-link>
      </div>
    </div>

    <!-- TAMPILAN 1: TABEL PERTANYAAN YANG SUDAH DIJAWAB (cuma yang sudah dijawab yang bisa direvisi) -->
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
            <tr v-else-if="listDijawab.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                Belum ada pertanyaan yang sudah Anda jawab untuk jadwal ini.
              </td>
            </tr>
            <tr
              v-for="(item, index) in pagedPertanyaan"
              :key="item.id"
              v-else
              class="hover:bg-gray-50"
            >
              <td class="px-4 py-3 text-sm text-gray-800 text-center">
                {{ (halaman - 1) * perHalaman + index + 1 }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-800">
                <div
                  class="font-medium"
                  v-html="formatTeksBernomor(item.pertanyaan?.butir_pertanyaan)"
                ></div>
              </td>
              <td class="px-4 py-3 text-center text-sm">
                <span
                  v-if="item.jawaban?.status_temuan"
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
                <p
                  v-if="item.jawaban?.direvisi_pada"
                  class="mt-1 text-xs font-medium text-orange-600"
                >
                  Direvisi pada {{ formatDirevisiPada(item.jawaban.direvisi_pada) }}
                </p>
              </td>
              <td class="px-4 py-3 text-center text-sm">
                <button
                  @click="bukaRevisi(item)"
                  class="px-3 py-1.5 border border-orange-300 text-orange-700 bg-orange-50 rounded-md text-xs font-medium hover:bg-orange-100"
                >
                  Revisi Jawaban
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :page="halaman" :total-pages="totalHalaman" @update:page="halaman = $event" />
    </div>

    <!-- TAMPILAN 2: FORM REVISI (dipindah dari LihatHasilAuditee.vue, 4 Okt 2026) -->
    <div
      v-else
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <div class="mb-6 bg-white p-5 border border-l-4 border-l-blue-500 rounded-md shadow-sm">
        <h3 class="text-sm font-bold text-blue-800 mb-2">Pernyataan Standar:</h3>
        <div
          class="text-sm text-gray-700 font-medium mb-3"
          v-html="formatTeksBernomor(soalAktif.pertanyaan?.pertanyaan)"
        ></div>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Butir Pertanyaan:</h3>
        <div
          class="text-sm text-gray-700"
          v-html="formatTeksBernomor(soalAktif.pertanyaan?.butir_pertanyaan)"
        ></div>
      </div>

      <!-- Komentar/penilaian Auditor (4 Okt 2026) - biar Auditee tahu apa yang perlu diperbaiki -->
      <div class="mb-6 bg-amber-50 p-4 border border-l-4 border-l-amber-500 rounded-md">
        <h3 class="text-xs font-bold text-amber-800 uppercase mb-2">
          Komentar / Penilaian Auditor
          <span
            v-if="soalAktif.jawaban?.status_temuan"
            class="ml-2 px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 normal-case"
          >
            {{ soalAktif.jawaban.status_temuan }}
          </span>
        </h3>
        <div
          v-if="soalAktif.jawaban?.penilaian_auditor"
          class="text-sm text-gray-700 whitespace-pre-line"
          v-html="formatTeksBernomor(soalAktif.jawaban.penilaian_auditor)"
        ></div>
        <p v-else class="text-sm text-gray-500 italic">
          Auditor belum memberikan komentar/penilaian.
        </p>
      </div>

      <form @submit.prevent="submitRevisi" class="space-y-4">
        <p v-if="soalAktif.jawaban?.direvisi_pada" class="text-xs font-semibold text-orange-700">
          Terakhir direvisi pada {{ formatDirevisiPada(soalAktif.jawaban.direvisi_pada) }}
        </p>

        <div>
          <label class="block text-sm font-semibold text-gray-800 mb-1">
            Jawaban / Kondisi Saat Ini <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="formRevisi.jawaban"
            rows="5"
            class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
            required
          ></textarea>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-800 mb-1">
            Link Bukti Dokumen (Google Drive) <span class="text-red-500">*</span>
          </label>
          <input
            type="url"
            v-model="formRevisi.link_bukti"
            class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="tutupRevisi"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700"
          >
            Kembali ke Daftar Pertanyaan
          </button>
          <button
            type="submit"
            :disabled="isSubmittingRevisi"
            class="px-5 py-2 bg-orange-600 text-white rounded-md text-sm font-medium hover:bg-orange-700 disabled:opacity-50"
          >
            {{ isSubmittingRevisi ? 'Mengirim...' : 'Kirim Revisi ke Auditor' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
// Halaman BARU (4 Okt 2026, permintaan user: "revisi auditee jadi halaman baru bukan gabung sama
// lihat hasil"). Logic revisi (bukaRevisi/submitRevisi/pisahDeskripsiHasil) dipindah APA ADANYA
// dari LihatHasilAuditee.vue - endpoint & aturan backend TIDAK berubah (POST
// /auditee/jawaban/store, JawabanController::storeAuditee() otomatis menganggap request sebagai
// revisi kalau deskripsi_hasil sudah pernah terisi). Daftar di sini cuma soal yang SUDAH dijawab
// (apa pun status penilaiannya), sesuai aturan revisi yang berlaku sejak 1 Okt 2026.
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'
import Pagination from '@/components/Pagination.vue'
import { usePagination } from '@/composables/usePagination'
import { formatTeksBernomor } from '@/utils/formatTeksBernomor'
import { formatDirevisiPada } from '@/utils/formatDirevisi'

const route = useRoute()
const jadwalId = route.query.id

const listPertanyaan = ref([])
const soalAktif = ref(null)
const isLoading = ref(false)

const listDijawab = computed(() => listPertanyaan.value.filter((i) => i.jawaban?.deskripsi_hasil))

const {
  page: halaman,
  totalPages: totalHalaman,
  pagedItems: pagedPertanyaan,
  perPage: perHalaman,
} = usePagination(listDijawab, 20)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const fetchListPertanyaan = async () => {
  isLoading.value = true
  try {
    const res = await axiosClient.get(`/jadwal-audit/${jadwalId}/pertanyaan`)
    if (gagal(res)) return
    listPertanyaan.value = res.data
  } finally {
    isLoading.value = false
  }
}

const isSubmittingRevisi = ref(false)
const formRevisi = reactive({ jawaban: '', link_bukti: '' })

// deskripsi_hasil tersimpan gabungan "<jawaban>\n\nLink Bukti Dokumen: <url>" (storeAuditee()) -
// sama persis pola pemisah di DeskripsiHasilComponent.vue & NilaiInstrumenAuditor.vue.
const MARKER_LINK_BUKTI = '\n\nLink Bukti Dokumen: '
const pisahDeskripsiHasil = (text) => {
  const idx = (text || '').indexOf(MARKER_LINK_BUKTI)
  if (idx < 0) return { jawaban: text || '', link_bukti: '' }
  return {
    jawaban: text.slice(0, idx),
    link_bukti: text.slice(idx + MARKER_LINK_BUKTI.length),
  }
}

const bukaRevisi = (item) => {
  const { jawaban, link_bukti } = pisahDeskripsiHasil(item.jawaban?.deskripsi_hasil)
  formRevisi.jawaban = jawaban
  formRevisi.link_bukti = link_bukti
  soalAktif.value = item
}

const tutupRevisi = () => {
  soalAktif.value = null
}

const submitRevisi = async () => {
  isSubmittingRevisi.value = true
  try {
    const res = await axiosClient.post('/auditee/jawaban/store', {
      jadwal_spmi_id: jadwalId,
      pertanyaan_id: soalAktif.value.pertanyaan_id, // bank_pertanyaans.id, sama seperti IsiInstrumenAuditee.vue
      jawaban: formRevisi.jawaban,
      link_bukti: formRevisi.link_bukti,
    })
    if (gagal(res)) return

    // Toast sukses sudah otomatis dari interceptor axios.js. Habis kirim, balik ke daftar.
    soalAktif.value = null
    await fetchListPertanyaan()
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
